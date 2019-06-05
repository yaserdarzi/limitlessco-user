<?php

namespace App\Http\Controllers\Api\V1\CP\Api;

use App\Api;
use App\ApiWallet;
use App\ApiWalletInvoice;
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
        $income = 0;
        $incomeApi = 0;
        $priceAll = 0;
        $percentAll = 0;
        $startExplode = explode('/', $request->input('start_date'));
        $endExplode = explode('/', $request->input('end_date'));
        $start_date = \Morilog\Jalali\CalendarUtils::toGregorian($startExplode[0], $startExplode[1], $startExplode[2]);
        $end_date = \Morilog\Jalali\CalendarUtils::toGregorian($endExplode[0], $endExplode[1], $endExplode[2]);
        $startDay = date_create(date('Y-m-d', strtotime($start_date[0] . '-' . $start_date[1] . '-' . $start_date[2])));
        $endDay = date_create(date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2])));
        $diff = date_diff($startDay, $endDay);
        for ($i = 0; $i <= $diff->days; $i++) {
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
            ->whereBetween('date', [$startDay, $endDay])
            ->where([
                'status' => Constants::STATUS_ACTIVE,
                'room_id' => $request->input('room_id'),
            ])
            ->where('capacity_remaining', '>=', 1)
            ->get();
        foreach ($roomEpisode as $key => $value) {
            $priceAll = $priceAll + $value->price;
            $percent = 0;
            if ($value->type_percent == Constants::TYPE_PRICE) {
                $percentAll = $percentAll + $value->percent;
                $percent = $value->percent;
            } elseif ($value->type_percent == Constants::TYPE_PERCENT) {
                if ($value->percent < 100)
                    $percentAll += ($value->percent / 100) * $value->price;
                else
                    $percentAll = $percentAll + $value->price;
            }
            $supplierSales = SupplierSales::
            join(Constants::SALES_DB, Constants::SALES_DB . '.id', '=', Constants::SUPPLIER_SALES_DB . '.sales_id')
                ->where([
                    'status' => Constants::STATUS_ACTIVE,
                    'type' => Constants::SALES_TYPE_API,
                    'supplier_id' => $value->supplier_id
                ])->first();
            if ($supplierSales)
                if ($supplierSales->type_price == Constants::TYPE_PERCENT)
                    $income += ($supplierSales->percent / 100) * ($value->price - $percent);
                elseif ($supplierSales->type_price == Constants::TYPE_PRICE)
                    $income = $income + $supplierSales->percent;
            if ($api->type == Constants::TYPE_PERCENT)
                $incomeApi += ($api->percent / 100) * ($value->price - $percent);
            elseif ($api->type == Constants::TYPE_PRICE)
                $incomeApi = $incomeApi + $api->price;
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
            'shopping_id' => Constants::APP_NAME_HOTEL . "-" . $request->input('room_id'),
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
            'price_fee' => 0,
            'percent_fee' => 0,
            'title' => $hotel->name,
            'title_more' => $room->title,
            'count' => 1,
            'price_all' => $priceAll * 1,
            'percent_all' => $percentAll * 1,
            'income' => intval($income * 1),
            'income_api' => intval($incomeApi * 1),
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
