<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\AgencyAgency;
use App\AgencyAgencyCategory;
use App\AgencyAgencyCategoryCommission;
use App\Commission;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class AgencyAgencyCategoryController extends ApiController
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $agencyAgencyCategory = AgencyAgencyCategory::
        where(['agency_id' => $request->input('agency_id')])->get();
        return $this->respond($agencyAgencyCategory);
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('title'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن عنوان اجباری می باشد.'
            );
        dd($request->input('commission'));
        if (!$request->input('commission'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد  کمیسیون اجباری می باشد.'
            );
//        dd(json_decode($request->input('commission')));
        foreach (json_decode($request->input('commission')) as $item)
            foreach ((array)$item as $key => $value) {
                $commission = Commission::where([
                    'shopping_id' => $key,
                    'customer_id' => Constants::SALES_TYPE_AGENCY . '-' . $request->input('agency_id')
                ])->first();
                if ($commission)
                    if ($commission->percent < $value)
                        throw new ApiException(
                            ApiException::EXCEPTION_NOT_FOUND_404,
                            'کاربر گرامی ، درصد کمیسیون بیشتر از حد مجاز است .'
                        );
            }
        $agencyAgencyCategory = AgencyAgencyCategory::create([
            'agency_id' => $request->input('agency_id'),
            'title' => $request->input('title'),
            'type_price' => Constants::TYPE_PERCENT,
            'percent' => 0
        ]);
        foreach (json_decode($request->input('commission')) as $item)
            foreach ((array)$item as $key => $value) {
                $commission = Commission::where([
                    'shopping_id' => $key,
                    'customer_id' => Constants::SALES_TYPE_AGENCY . '-' . $request->input('agency_id')
                ])->first();
                if ($commission->percent < $value)
                    throw new ApiException(
                        ApiException::EXCEPTION_NOT_FOUND_404,
                        'کاربر گرامی ، درصد کمیسیون بیشتر از حد مجاز است .'
                    );
                if ($value != 0)
                    AgencyAgencyCategoryCommission::create([
                        'agency_agency_category_id' => $agencyAgencyCategory->id,
                        'shopping_id' => $key,
                        'type' => Constants::TYPE_PERCENT,
                        'percent' => $value,
                    ]);
            }
        return $this->respond(["status" => "success"]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $agencyAgencyCategory = AgencyAgencyCategory::
        where([
            'agency_id' => $request->input('agency_id'),
            'id' => $id
        ])->first();
        return $this->respond($agencyAgencyCategory);
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('title'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن عنوان اجباری می باشد.'
            );
        if (!$request->input('commission'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد  کمیسیون اجباری می باشد.'
            );
        switch ($request->input('type_percent')) {
            case Constants::TYPE_PRICE:
                $typePercent = Constants::TYPE_PRICE;
                $array = ['price' => $this->help->priceNumberDigitsToNormal($request->input('commission'))];
                break;
            case Constants::TYPE_PERCENT:
                $typePercent = Constants::TYPE_PERCENT;
                $array = ['percent' => $this->help->priceNumberDigitsToNormal($request->input('commission'))];
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، وارد کردن نوع تخفیف (تومان یا درصد) اجباری می باشد.'
                );
        }
        $data = array(
            'title' => $request->input('title'),
            'type_price' => $typePercent,
        );
        $data = array_merge($array, $data);
        AgencyAgencyCategory::where([
            'agency_id' => $request->input('agency_id'),
            'id' => $id
        ])->update($data);
        return $this->respond(["status" => "success"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (AgencyAgency::where(['agency_agency_category_id' => $id])->count())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی امکان حذف برای گروهی که آژانس دارد امکان پذیر نمی باشد."
            );
        if (!AgencyAgencyCategory::where(['id' => $id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی شما دسترسی لازم برای حذف را ندارید."
            );
        AgencyAgencyCategory::where(['id' => $id, 'agency_id' => $request->input('agency_id')])->delete();
        return $this->respond(["status" => "success"]);
    }

    ///////////////////public function///////////////////////

    public function getCommission(Request $request)
    {
        $commission = Commission::where('customer_id', Constants::SALES_TYPE_AGENCY . '-' . $request->input('agency_id'))
            ->orderBy('shopping_id')->get()->map(function ($value) {
                switch (explode('-', $value->shopping_id)[0]) {
                    case Constants::APP_NAME_HOTEL:
                        $hotel = DB::connection(Constants::CONNECTION_HOTEL)
                            ->table(Constants::APP_HOTEL_DB_HOTEL_DB)
                            ->where('id', explode('-', $value->shopping_id)[1])
                            ->first();
                        $room = DB::connection(Constants::CONNECTION_HOTEL)
                            ->table(Constants::APP_HOTEL_DB_ROOM_DB)
                            ->where('id', explode('-', $value->shopping_id)[2])
                            ->first();
                        if ($hotel->logo) {
                            $value->image_thumb = env('CDN_HOTEL_URL') . '/files/hotel/thumb/' . $hotel->logo;
                            $value->image = env('CDN_HOTEL_URL') . '/files/hotel/' . $hotel->logo;
                        }
                        $value->title = $hotel->name . " " . $room->title;
                        $value->desc = $hotel->about;
                        break;
                    case Constants::APP_NAME_ENTERTAINMENT:
                        $product = DB::connection(Constants::CONNECTION_ENTERTAINMENT)
                            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_DB)
                            ->where('id', explode('-', $value->shopping_id)[1])
                            ->first();
                        if ($product->image) {
                            $value->image_thumb = env('CDN_ENTERTAINMENT_URL') . '/files/product/thumb/' . $product->image;
                            $value->image = env('CDN_ENTERTAINMENT_URL') . '/files/product/' . $product->image;
                        }
                        $value->title = $product->title;
                        $value->desc = $product->small_desc;
                        break;
                }
                return $value;
            });
        return $this->respond($commission);
    }
}
