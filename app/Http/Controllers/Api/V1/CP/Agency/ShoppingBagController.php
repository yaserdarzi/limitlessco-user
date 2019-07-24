<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\Agency;
use App\AgencyUser;
use App\Commission;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Sales;
use App\ShoppingBag;
use App\ShoppingBagExpire;
use App\SupplierAgency;
use App\SupplierSales;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\CalendarUtils;

class ShoppingBagController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $shoppingBagExpire = ShoppingBagExpire::where([
            'customer_id' => Constants::AGENCY_DB . "-" . $request->input('agency_id') . "-" . $request->input('user_id')
        ])->first();
        $shoppingBag = [];
        $incomeAgency = 0;
        $incomeYou = 0;
        $priceAll = 0;
        $percentAll = 0;
        $countAll = 0;
        if ($shoppingBagExpire) {
            $shoppingBag = ShoppingBag::where('customer_id', $shoppingBagExpire->customer_id)->get();
            foreach ($shoppingBag as $value) {
                $priceAll = $priceAll + $value->price_all;
                $percentAll = $percentAll + $value->percent_all;
                $incomeAgency = $incomeAgency + $value->income_all;
                $incomeYou = $incomeYou + $value->income_you;
                $countAll = $countAll + $value->count;
                $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
                $value->date_end_persian = null;
                if ($value->date_end)
                    $value->date_end_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date_end));
                $value->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->created_at));
            }
        }
        $pricePayment = $priceAll - $percentAll - $incomeAgency;
        return $this->respond([
            "pricePayment" => $pricePayment,
            "countAll" => $countAll,
            "priceAll" => $priceAll,
            "percentAll" => $percentAll,
            "incomeAgency" => $incomeAgency,
            "incomeYou" => $incomeYou,
            "shoppingBag" => $shoppingBag
        ]);
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
        //get supplier
        $agency = Agency::where('id', $request->input('agency_id'))->first();
        $supplier_id = [];
        if (in_array(Constants::AGENCY_INTRODUCTION_SALES, $agency->introduction)) {
            $type = Constants::SALES_TYPE_AGENCY;
            $sales = Sales::where(
                'type', $type
            )->first();
            $supplier_id = array_unique(array_merge($supplier_id, SupplierSales::
            where('capacity_percent', '!=', 0)
                ->where(['status' => Constants::STATUS_ACTIVE, 'sales_id' => $sales->id])
                ->pluck('supplier_id')->toArray()));
        }
        if (in_array(Constants::AGENCY_INTRODUCTION_SUPPLIER, $agency->introduction))
            $supplier_id = array_unique(array_merge($supplier_id, SupplierAgency::
            where('capacity_percent', '!=', 0)
                ->where(['status' => Constants::STATUS_ACTIVE, 'agency_id' => $request->input('agency_id')])
                ->pluck('supplier_id')->toArray()));
        switch ($request->input('app_title')) {
            case Constants::APP_NAME_HOTEL:
                return $this->respond($this->addToShoppingBagHotel($supplier_id, $request));
                break;
            case Constants::APP_NAME_ENTERTAINMENT:
                return $this->respond($this->addToShoppingBagEntertainment($supplier_id, $request));
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
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id');
        if (!ShoppingBag::where(['id' => $id, 'customer_id' => $customer_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        $shoppingBag = ShoppingBag::where([
            'id' => $id,
            'customer_id' => $customer_id
        ])->get();
        if (sizeof($shoppingBag))
            foreach ($shoppingBag as $value)
                switch (explode('-', $value->shopping_id)[0]) {
                    case Constants::APP_NAME_HOTEL:
                        $this->hotelCheck($value);
                        break;
                    case Constants::APP_NAME_ENTERTAINMENT:
                        $this->entertainmentCheck($value);
                        break;
                }
        ShoppingBag::where(['id' => $id, 'customer_id' => $customer_id])->delete();
        return $this->respond(['status' => 'success']);
    }

    ///////////////////public function///////////////////////

    public function destroyAll(Request $request)
    {
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id');
        $shoppingBag = ShoppingBag::where([
            'customer_id' => $customer_id
        ])->get();
        if (sizeof($shoppingBag))
            foreach ($shoppingBag as $value)
                switch (explode('-', $value->shopping_id)[0]) {
                    case Constants::APP_NAME_HOTEL:
                        $this->hotelCheck($value);
                        break;
                    case Constants::APP_NAME_ENTERTAINMENT:
                        $this->entertainmentCheck($value);
                        break;
                }
        ShoppingBag::where(['customer_id' => $customer_id])->delete();
        ShoppingBagExpire::where(['customer_id' => $customer_id])->delete();
        return $this->respond(['status' => 'success']);
    }

    ///////////////////private function///////////////////////

    private function expireShopping($customer_id)
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
                'customer_id' => $customer_id,
                'expire_time' => date('Y-m-d H:i:s', strtotime("+10 minutes")),
                'status' => Constants::SHOPPING_STATUS_SHOPPING
            ]);
    }
    //
    //Hotel
    //
    private function addToShoppingBagHotel($supplier_id, Request $request)
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
            ->whereIn('supplier_id', $supplier_id)
            ->whereBetween('date', [$startDay, date('Y-m-d', strtotime('-1 day', strtotime($endDayDate)))])
            ->where([
                'status' => Constants::STATUS_ACTIVE,
                'room_id' => $request->input('room_id'),
            ])
            ->where('capacity_remaining', '>=', $request->input('count'))
            ->get();
        $priceAll = 0;
        $percentAll = 0;
        $income = 0;
        $incomeAgency = 0;
        $incomeYou = 0;
        $addPrice = 0;
        $price_income = 0;
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id');
        foreach ($roomEpisode as $key => $value) {
            $shopping_id_commission = $request->input('app_title') . "-" . $value->hotel_id . "-" . $value->room_id;
            $commission = Commission::where(['customer_id' => $customer_id, 'shopping_id' => $shopping_id_commission])->first();
            if (!$commission)
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، درصد کمیسیون شما مشخص نشده است لطفا با پیشتیبانی تماس حاصل فرمایید.'
                );
            $value->commission = $commission;
            $priceAll += $value->price_power_up;
            $price_income += $value->price_power_up;
            $price = $value->price_power_up;
            if ($request->input('is_capacity') == "true") {
                $addPrice += $value->add_price;
                $priceAll += $addPrice;
                $price_income += $addPrice;
                $price += $addPrice;
            }
            if ($commission->type == Constants::TYPE_PERCENT) {
                if ($commission->percent < 100)
                    $incomeAgency += intval(($commission->percent / 100) * $price);
            } elseif ($commission->type == Constants::TYPE_PRICE)
                $incomeAgency = $incomeAgency + $commission->price;
            $agencyUser = AgencyUser::where([
                'user_id' => $request->input('user_id'),
                'agency_id' => $request->input('agency_id')
            ])->first();
            if ($agencyUser)
                if ($agencyUser->type == Constants::TYPE_PERCENT)
                    if ($agencyUser->percent < 100) {
                        if ($agencyUser->percent != 0)
                            $incomeYou = ($agencyUser->percent / 100) * $incomeAgency;
                    } else
                        $incomeYou = $incomeAgency;
                elseif ($agencyUser->type == Constants::TYPE_PRICE)
                    $incomeYou = $incomeYou + $agencyUser->price;
            $priceAll += $incomeAgency;
            $income += $priceAll - $price_income;
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
        if ($shopping = ShoppingBag::where(['date' => $startDay->format('Y-m-d'), 'date_end' => $endDay->format('Y-m-d'), 'shopping_id' => $shopping_id, 'customer_id' => Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id')])->first())
            ShoppingBag::
            where([
                'date' => $startDay->format('Y-m-d'),
                'date_end' => $endDay->format('Y-m-d'),
                'shopping_id' => $shopping_id,
                'customer_id' => Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id')
            ])->update([
                'count' => $shopping->count + $request->input('count'),
                'price_income' => $price_income * ($shopping->count + $request->input('count')),
                'price_all' => $priceAll * ($shopping->count + $request->input('count')),
                'percent_all' => $percentAll * ($shopping->count + $request->input('count')),
                'income' => $income * ($shopping->count + $request->input('count')),
                'income_all' => $incomeAgency * ($shopping->count + $request->input('count')),
                'income_you' => $incomeYou * ($shopping->count + $request->input('count'))
            ]);
        else
            ShoppingBag::create([
                'shopping_id' => $shopping_id,
                'customer_id' => Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id'),
                'title' => $hotel->name,
                'title_more' => $title_more,
                'date' => $startDay->format('Y-m-d'),
                'date_end' => $endDay->format('Y-m-d'),
                'count' => $request->input('count'),
                'price_income' => $price_income * $request->input('count'),
                'price_all' => $priceAll * $request->input('count'),
                'percent_all' => $percentAll * $request->input('count'),
                'income' => intval($income * $request->input('count')),
                'income_all' => intval($incomeAgency * $request->input('count')),
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
        $this->expireShopping(Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id'));
        return ["status" => "success"];
    }

    private function hotelCheck($shoppingBag)
    {
        foreach ($shoppingBag->shopping->roomEpisode as $value) {
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->decrement('capacity_filled', $shoppingBag->count);
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->increment('capacity_remaining', $shoppingBag->count);
        }
        ShoppingBag::where('id', $shoppingBag->id)->delete();
    }

    //
    //Entertainment
    //
    private function addToShoppingBagEntertainment($supplier_id, Request $request)
    {
        if (!$request->input('episode_id'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره سانس اجباری می باشد.'
            );
        $count = intval($this->help->normalizePhoneNumber($request->input('count')));
        $count_child = intval($this->help->normalizePhoneNumber($request->input('count_child')));
        $count_baby = intval($this->help->normalizePhoneNumber($request->input('count_baby')));
        $productEpisode = DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_EPISODE_DB)
            ->whereIn('supplier_id', $supplier_id)
            ->where([
                'status' => Constants::STATUS_ACTIVE,
                'id' => $request->input('episode_id'),
            ])->where('capacity_remaining', '>=', intval($count + $count_child))
            ->first();
        if (!$productEpisode)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، سانس مورد نظر خالی نمی باشد.'
            );
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id');
        $shopping_id_commission = $request->input('app_title') . "-" . $productEpisode->product_id;
        $commission = Commission::where(['customer_id' => $customer_id, 'shopping_id' => $shopping_id_commission])->first();
        if (!$commission)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، درصد کمیسیون شما مشخص نشده است لطفا با پیشتیبانی تماس حاصل فرمایید.'
            );
        $productEpisode->commission = $commission;
        $percentAll = 0;
        $income = 0;
        $incomeAgency = 0;
        $incomeYou = 0;
        $priceAll = intval(
            intval($productEpisode->price_adult_power_up * $count) +
            intval($productEpisode->price_child_power_up * $count_child) +
            intval($productEpisode->price_baby_power_up * $count_baby)
        );
        $price_income = intval(
            intval($productEpisode->price_adult_power_up * $count) +
            intval($productEpisode->price_child_power_up * $count_child) +
            intval($productEpisode->price_baby_power_up * $count_baby)
        );
        if ($commission->type == Constants::TYPE_PERCENT) {
            if ($commission->percent < 100)
                $incomeAgency += intval(($commission->percent / 100) * $price_income);
        } elseif ($commission->type == Constants::TYPE_PRICE)
            $incomeAgency = $incomeAgency + $commission->price;
        $agencyUser = AgencyUser::where([
            'user_id' => $request->input('user_id'),
            'agency_id' => $request->input('agency_id')
        ])->first();
        if ($agencyUser)
            if ($agencyUser->type == Constants::TYPE_PERCENT)
                if ($agencyUser->percent < 100) {
                    if ($agencyUser->percent != 0)
                        $incomeYou = ($agencyUser->percent / 100) * $incomeAgency;
                } else
                    $incomeYou = $incomeAgency;
            elseif ($agencyUser->type == Constants::TYPE_PRICE)
                $incomeYou = $incomeYou + $agencyUser->price;
        $priceAll += $incomeAgency;
        $income += $priceAll - $price_income;
        $product = DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_DB)
            ->where('id', $productEpisode->product_id)
            ->first();
        $shopping_id = $request->input('app_title') . "-" . $request->input('episode_id');
        if ($shopping = ShoppingBag::where(['shopping_id' => $shopping_id, 'customer_id' => Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id')])->first())
            ShoppingBag::
            where([
                'shopping_id' => $shopping_id,
                'customer_id' => Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id')
            ])->update([
                'count' => $shopping->count + ($count + $count_child),
                'price_income' => $price_income + $shopping->price_income,
                'price_all' => $priceAll + $shopping->price_all,
                'percent_all' => $percentAll + $shopping->percent_all,
                'income' => $income + $shopping->income,
                'income_all' => $incomeAgency + $shopping->income_all,
                'income_you' => $incomeYou + $shopping->income_you
            ]);
        else
            ShoppingBag::create([
                'shopping_id' => $shopping_id,
                'customer_id' => Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id'),
                'title' => $product->title,
                'title_more' => $productEpisode->title,
                'date' => $productEpisode->date,
                'date_end' => $productEpisode->date,
                'start_hours' => $productEpisode->start_hours,
                'end_hours' => $productEpisode->end_hours,
                'count' => ($count + $count_child),
                'price_income' => $price_income,
                'price_all' => $priceAll,
                'percent_all' => $percentAll,
                'income' => intval($income),
                'income_all' => intval($incomeAgency),
                'income_you' => intval($incomeYou),
                'shopping' => [
                    "productEpisode" => (array)$productEpisode,
                    "product" => (array)$product,
                    "price_count" => [
                        "adult" => ["price" => $productEpisode->price_adult, "count" => $count],
                        "child" => ["price" => $productEpisode->price_child, "count" => $count_child],
                        "baby" => ["price" => $productEpisode->price_baby, "count" => $count_baby]
                    ]
                ]
            ]);
        DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_EPISODE_DB)
            ->where('id', $productEpisode->id)
            ->increment('capacity_filled', ($count + $count_child));
        DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_EPISODE_DB)
            ->where('id', $productEpisode->id)
            ->decrement('capacity_remaining', ($count + $count_child));
        $this->expireShopping(Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-" . $request->input('user_id'));
        return ["status" => "success"];
    }

    private function entertainmentCheck($shoppingBag)
    {
        DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_EPISODE_DB)
            ->where('id', $shoppingBag->shopping->productEpisode->id)
            ->decrement('capacity_filled', $shoppingBag->count);
        DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_EPISODE_DB)
            ->where('id', $shoppingBag->shopping->productEpisode->id)
            ->increment('capacity_remaining', $shoppingBag->count);
        ShoppingBag::where('id', $shoppingBag->id)->delete();
    }
}
