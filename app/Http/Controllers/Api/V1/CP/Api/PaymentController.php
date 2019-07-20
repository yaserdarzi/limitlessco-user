<?php

namespace App\Http\Controllers\Api\V1\CP\Api;

use App\Api;
use App\ApiWallet;
use App\ApiWalletInvoice;
use App\Commission;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Sales;
use App\Shopping;
use App\ShoppingInvoice;
use App\SupplierSales;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class PaymentController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        //
    }

    ///////////////////public function///////////////////////


    public function PaymentHotel(Request $request)
    {
        //get supplier
        $sales = Sales::where(
            'type', Constants::SALES_TYPE_API
        )->first();
        $supplier_id = SupplierSales::
        where('capacity_percent', '!=', 0)
            ->where(['status' => Constants::STATUS_ACTIVE, 'sales_id' => $sales->id])
            ->pluck('supplier_id');
        if (!$request->input('room_id'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره اتاق اجباری می باشد.'
            );
        if (!$request->input('start_date'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تاریخ شروع اجباری می باشد.'
            );
        if (!$request->input('end_date'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تاریخ پایان اجباری می باشد.'
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام مشتری اجباری می باشد.'
            );
        if (!$request->input('phone'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره همراه مشتری اجباری می باشد.'
            );
        $api = Api::where('id', $request->input('api_id'))->first();
        $customer_id = Constants::API_DB . "-" . $request->input('api_id') . "-" . $request->input('user_id');
        $startExplode = explode('/', $request->input('start_date'));
        $endExplode = explode('/', $request->input('end_date'));
        $start_date = \Morilog\Jalali\CalendarUtils::toGregorian($startExplode[0], $startExplode[1], $startExplode[2]);
        $end_date = \Morilog\Jalali\CalendarUtils::toGregorian($endExplode[0], $endExplode[1], $endExplode[2]);
        $startDay = date_create(date('Y-m-d', strtotime($start_date[0] . '-' . $start_date[1] . '-' . $start_date[2])));
        $endDay = date_create(date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2])));
        $endDayDate = date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2]));
        $diff = date_diff($startDay, $endDay);
        for ($i = 0; $i < $diff->days; $i++) {
            $date = strtotime(date('Y-m-d', strtotime($startDay->format('Y-m-d') . " +" . $i . " days")));
            $roomToday = DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->whereIn('supplier_id', $supplier_id)
                ->where([
                    'status' => Constants::STATUS_ACTIVE,
                    'date' => date('Y-m-d', $date),
                    'room_id' => $request->input('room_id'),
                ])
                ->where('capacity_remaining', '>=', 1)
                ->groupBy('room_id')
                ->pluck('room_id');
            if (!sizeof($roomToday))
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، اتاق مورد نظر خالی نمی باشد.'
                );

        }
        $roomEpisode = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
            ->whereIn('supplier_id', $supplier_id)
            ->whereBetween('date', [$startDay, date('Y-m-d', strtotime('-1 day', strtotime($endDayDate)))])
            ->where([
                'status' => Constants::STATUS_ACTIVE,
                'room_id' => $request->input('room_id'),
            ])->where('capacity_remaining', '>=', 1)
            ->get();
        $priceAll = 0;
        $percentAll = 0;
        $income = 0;
        $incomeApi = 0;
        $addPrice = 0;
        $price_income = 0;
        $customer_id = Constants::SALES_TYPE_API . "-" . $request->input('api_id');
        foreach ($roomEpisode as $key => $value) {
            $shopping_id_commission = Constants::APP_NAME_HOTEL . "-" . $value->hotel_id . "-" . $value->room_id;
            $commission = Commission::where(['customer_id' => $customer_id, 'shopping_id' => $shopping_id_commission])->first();
            if (!$commission)
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، درصد کمیسیون شما مشخص نشده است لطفا با پیشتیبانی تماس حاصل فرمایید.'
                );
            $value->commission = $commission;
            $percent = 0;
            if ($commission->is_price_power_up) {
                $price = $value->price_power_up;
                $priceAll = $priceAll + $value->price_power_up;
                $price_income = $price_income + $value->price_power_up;
                if ($value->type_percent == Constants::TYPE_PRICE) {
                    $percentAll = $percentAll + $value->percent;
                    $percent = $value->percent;
                } elseif ($value->type_percent == Constants::TYPE_PERCENT) {
                    if ($value->percent != 0) {
                        $percentAll += ($value->percent / 100) * $value->price_power_up;
                        $percent = ($value->percent / 100) * $value->price_power_up;
                    }
                }
            } else {
                $price = $value->price;
                $priceAll = $priceAll + $value->price;
                $price_income = $price_income + $value->price_power_up;
                if ($value->type_percent == Constants::TYPE_PRICE) {
                    $percentAll = $percentAll + $value->percent;
                    $percent = $value->percent;
                } elseif ($value->type_percent == Constants::TYPE_PERCENT) {
                    if ($value->percent != 0) {
                        $percentAll += ($value->percent / 100) * $value->price;
                        $percent = ($value->percent / 100) * $value->price;
                    }
                }
            }
            if ($request->input('is_capacity') == "true") {
                $addPrice += $value->add_price;
                $priceAll += $addPrice;
                $price_income += $addPrice;
                $price += $addPrice;
            }
            $commissionSupplier = Commission::where([
                'customer_id' => Constants::SALES_TYPE_SUPPLIER . '-' . $value->supplier_id,
                'shopping_id' => $shopping_id_commission,
            ])->first();
            if ($commissionSupplier)
                if ($commissionSupplier->type == Constants::TYPE_PERCENT) {
                    if ($commissionSupplier->percent != 0)
                        $income += intval(($commissionSupplier->percent / 100) * $value->price_power_up);
                } elseif ($commissionSupplier->type == Constants::TYPE_PRICE)
                    $income = $income + $commissionSupplier->price;
            if ($commission->type == Constants::TYPE_PERCENT) {
                if ($commission->percent < 100)
                    $incomeApi += intval(($commission->percent / 100) * $price);
            } elseif ($commission->type == Constants::TYPE_PRICE)
                $incomeApi = $incomeApi + $commission->price;
        }
        $room = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_ROOM_DB)
            ->where('id', $request->input('room_id'))
            ->first();
        $hotel = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_HOTEL_DB)
            ->where('id', $room->hotel_id)
            ->first();
        $shopping_id = $request->input('app_title') . "-" . $request->input('room_id');
        $title_more = $room->title;
        if ($request->input('is_capacity') == "true") {
            $shopping_id = $shopping_id . '-' . $request->input('is_capacity');
            $title_more = $room->title . Constants::ADDED_BED;
        }
        $room = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_ROOM_DB)
            ->where('id', $request->input('room_id'))
            ->first();
        $hotel = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_HOTEL_DB)
            ->where('id', $room->hotel_id)
            ->first();
        $pricePayment = $priceAll - $percentAll - $incomeApi;
        $wallet = ApiWallet::where(['api_id' => $request->input('api_id')])->first();
        if ($wallet->price <= $pricePayment)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، موجودی کیف پول شما کافی نمی باشد.'
            );
        $helper = new Helpers();
        $shoppingInvoice = ShoppingInvoice::count();
        $shoppingPaymentToken = "s-" . ++$shoppingInvoice;
        $shoppingInvoice = ShoppingInvoice::create([
            'customer_id' => $customer_id,
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'count_all' => 1,
            'price_all' => $priceAll,
            'percent_all' => $percentAll,
            'income' => $income,
            'income_all' => $incomeApi,
            'income_you' => 0,
            'price_payment' => $pricePayment,
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_SUCCESS,
            'type' => Constants::INVOICE_TYPE_SHOPPING,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING,
            'payment_token' => $shoppingPaymentToken,
            'market' => Constants::INVOICE_MARKET_WALLET,
            'info' => ["shopping" => ["roomEpisode" => $roomEpisode->toArray(), "hotel" => (array)$hotel, "room" => (array)$room]],
        ]);
        $wallet = ApiWallet::where('api_id', $request->input('api_id'))->first();
        $walletPaymentTokenApiCount = ApiWalletInvoice::count();
        $walletPaymentTokenApi = "a-" . ++$walletPaymentTokenApiCount;
        ApiWalletInvoice::create([
            'api_id' => $request->input('api_id'),
            'wallet_id' => $wallet->id,
            'price_before' => $wallet->price,
            'price' => $pricePayment,
            'price_after' => intval($wallet->price - $pricePayment),
            'price_all' => $pricePayment,
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_SUCCESS,
            'type' => Constants::INVOICE_TYPE_SHOPPING,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING . " - " . Constants::INVOICE_INVOICE_STATUS_DECREMENT,
            'payment_token' => $walletPaymentTokenApi,
            'market' => Constants::INVOICE_MARKET_WALLET,
            'info' => ['wallet' => $wallet],
        ]);
        ApiWallet::where('api_id', $request->input('api_id'))->update([
            'price' => intval($wallet->price - $pricePayment)
        ]);
        Shopping::create([
            'shopping_id' => $shopping_id,
            'customer_id' => $customer_id,
            'supplier_id' => $supplier_id[0],
            'shopping_invoice_id' => $shoppingInvoice->id,
            'voucher' => $helper->voucher(Constants::APP_NAME_HOTEL),
            'name' => $shoppingInvoice->name,
            'phone' => $shoppingInvoice->phone,
            'date' => $startDay->format('Y-m-d'),
            'date_end' => $endDay->format('Y-m-d'),
            'start_hours' => "",
            'end_hours' => "",
            'percent_fee' => 0,
            'title' => $hotel->name,
            'title_more' => $title_more,
            'count' => 1,
            'price_income' => $price_income * 1,
            'price_all' => $priceAll * 1,
            'percent_all' => $percentAll * 1,
            'income' => intval($income * 1),
            'income_all' => intval($incomeApi * 1),
            'income_you' => intval(0 * 1),
            'price_payment' => $pricePayment,
            'status' => Constants::SHOPPING_STATUS_SUCCESS,
            'shopping' => ["roomEpisode" => $roomEpisode->toArray(), "hotel" => (array)$hotel, "room" => (array)$room]
        ]);
        foreach ($roomEpisode as $key => $value) {
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->increment('capacity_filled', 1);
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->decrement('capacity_remaining', 1);
        }
        return ["status" => "success", "paymentToken" => $shoppingPaymentToken];
    }

}
