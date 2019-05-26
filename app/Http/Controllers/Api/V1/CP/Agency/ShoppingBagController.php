<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Sales;
use App\ShoppingBag;
use App\ShoppingBagExpire;
use App\SupplierSales;
use App\SupplierUser;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class ShoppingBagController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $shoppingBagExpire = ShoppingBagExpire::where([
            'customer_id' => Constants::AGENCY_DB . "-" . $request->input('agency_id'),
            'status' => Constants::SHOPPING_STATUS_SHOPPING
        ])->first();
        $shoppingBag = [];
        $incomeAgency = 0;
        $incomeYou = 0;
        $priceAll = 0;
        $percentAll = 0;
        if ($shoppingBagExpire) {
            $shoppingBag = ShoppingBag::where('customer_id', $shoppingBagExpire->customer_id)->get();
            foreach ($shoppingBag as $value) {
                $priceAll = $priceAll + $value->price_all;
                $percentAll = $percentAll + $value->percent_all;
                $incomeAgency = $incomeAgency + $value->income_agency;
                $incomeYou = $incomeYou + $value->income_you;
            }
        }
        $pricePayment = $priceAll - $percentAll - $incomeAgency;
        return $this->respond(["pricePayment" => number_format($pricePayment), "priceAll" => number_format($priceAll), "percentAll" => number_format($percentAll), "incomeAgency" => number_format($incomeAgency), "incomeYou" => number_format($incomeYou), "shoppingBag" => $shoppingBag]);
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
        if (!$request->input('count'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تعداد اجباری می باشد.'
            );
        //get supplier
        $type = Constants::SALES_TYPE_AGENCY;
        $sales = Sales::where(
            'type', $type
        )->first();
        $supplier_id = SupplierSales::
        where('capacity_percent', '!=', 0)
            ->where(['status' => Constants::STATUS_ACTIVE, 'sales_id' => $sales->id])
            ->pluck('supplier_id');
        switch ($request->input('app_title')) {
            case Constants::APP_NAME_HOTEL:
                return $this->respond($this->addToShoppingBagHotel($supplier_id, $request));
                break;
        }
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


    ///////////////////private function///////////////////////

    private function expireShopping($app_id, $customer_id)
    {
        if (ShoppingBagExpire::where(['customer_id' => $customer_id])->exists())
            ShoppingBagExpire::
            where([
                'customer_id' => $customer_id
            ])->update([
                'expire_time' => date('Y-m-d H:i:s', strtotime("+10 minutes")),
                'status' => Constants::SHOPPING_STATUS_SHOPPING
            ]);
        else
            ShoppingBagExpire::create([
                'app_id' => $app_id,
                'customer_id' => $customer_id,
                'expire_time' => date('Y-m-d H:i:s', strtotime("+10 minutes")),
                'status' => Constants::SHOPPING_STATUS_SHOPPING
            ]);
    }

    private function addToShoppingBagHotel($supplier_id, $request)
    {
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
                ->where('app_id', $request->input('app_id'))
                ->whereIn('supplier_id', $supplier_id)
                ->where([
                    'status' => Constants::STATUS_ACTIVE,
                    'date' => date('Y-m-d', $date),
                    'room_id' => $request->input('room_id'),
                ])
                ->where('capacity_remaining', '>=', $request->input('count'))
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
            ->where('app_id', $request->input('app_id'))
            ->whereIn('supplier_id', $supplier_id)
            ->whereBetween('date', [$startDay, $endDay])
            ->where([
                'status' => Constants::STATUS_ACTIVE,
                'room_id' => $request->input('room_id'),
            ])
            ->where('capacity_remaining', '>=', $request->input('count'))
            ->get();
        $priceAll = 0;
        $percentAll = 0;
        $incomeAgency = 0;
        $incomeYou = 0;
        foreach ($roomEpisode as $key => $value) {
            $priceAll = $priceAll + $value->price;
            $percent = 0;
            if ($value->type_percent == Constants::TYPE_PRICE) {
                $percentAll = $percentAll + $value->percent;
                $percent = $value->percent;
            } elseif ($value->type_percent == Constants::TYPE_PERCENT) {
                if ($value->percent < 100) {
                    $floatPercent = floatval("0." . $value->percent);
                    $percentAll = $percentAll + ($value->price * $floatPercent);
                    $percent = ($value->price * $floatPercent);
                } else
                    $percentAll = $percentAll + $value->price;
            }
            $supplierSales = SupplierSales::
            join(Constants::SALES_DB, Constants::SALES_DB . '.id', '=', Constants::SUPPLIER_SALES_DB . '.sales_id')
                ->where([
                    'type' => Constants::SALES_TYPE_AGENCY,
                    'supplier_id' => $value->supplier_id
                ])->first();
            if ($supplierSales->type_price == Constants::TYPE_PERCENT)
                $incomeAgency += ($value->price - $percent) * floatval("0." . $supplierSales->percent);
            elseif ($supplierSales->type_price == Constants::TYPE_PRICE)
                $incomeAgency = $incomeAgency + $supplierSales->percent;
            $supplierUser = SupplierUser::where([
                'user_id' => $request->input('user_id'),
                'supplier_id' => $value->supplier_id
            ])->first();
            if ($supplierUser->type == Constants::TYPE_PERCENT)
                if ($supplierUser->percent != 100)
                    $incomeYou = $incomeAgency * floatval("0." . $supplierUser->percent);
                else
                    $incomeYou = $incomeAgency;
            elseif ($supplierUser->type == Constants::TYPE_PRICE)
                $incomeYou = $incomeYou + $supplierUser->price;
        }
        $room = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_ROOM_DB)
            ->where('id', $request->input('room_id'))
            ->first();
        $hotel = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_HOTEL_DB)
            ->where('id', $room->hotel_id)
            ->first();
        if ($shopping = ShoppingBag::where(['date' => $startDay->format('Y-m-d'), 'date_end' => $endDay->format('Y-m-d'), 'app_id' => $request->input('app_id'), 'shopping_id' => $request->input('app_title') . "-" . $request->input('room_id'), 'customer_id' => Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id')])->first())
            ShoppingBag::
            where([
                'app_id' => $request->input('app_id'),
                'date' => $startDay->format('Y-m-d'),
                'date_end' => $endDay->format('Y-m-d'),
                'shopping_id' => $request->input('app_title') . "-" . $request->input('room_id'),
                'customer_id' => Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id')
            ])->update([
                'count' => $shopping->count + $request->input('count'),
                'price_all' => $priceAll * ($shopping->count + $request->input('count')),
                'percent_all' => $percentAll * ($shopping->count + $request->input('count')),
                'income_agency' => $incomeAgency * ($shopping->count + $request->input('count')),
                'income_you' => $incomeYou * ($shopping->count + $request->input('count'))
            ]);
        else
            ShoppingBag::create([
                'app_id' => $request->input('app_id'),
                'shopping_id' => $request->input('app_title') . "-" . $request->input('room_id'),
                'customer_id' => Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id'),
                'title' => $hotel->name,
                'title_more' => $room->title,
                'date' => $startDay->format('Y-m-d'),
                'date_end' => $endDay->format('Y-m-d'),
                'count' => $request->input('count'),
                'price_all' => $priceAll * $request->input('count'),
                'percent_all' => $percentAll * $request->input('count'),
                'income_agency' => intval($incomeAgency * $request->input('count')),
                'income_you' => intval($incomeYou * $request->input('count')),
                'shopping' => ["roomEpisode" => $roomEpisode->toArray(), "hotel" => (array)$hotel, "room" => (array)$room]
            ]);
        foreach ($roomEpisode as $key => $value) {
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->increment('capacity_filled', $request->input('count'));
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->decrement('capacity_remaining', $request->input('count'));
        }
        $this->expireShopping($request->input('app_id'), Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id'));
        return ["status" => "success"];
    }
}
