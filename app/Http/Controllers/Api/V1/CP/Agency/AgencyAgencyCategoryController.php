<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\AgencyAgency;
use App\AgencyAgencyCategory;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;

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
            'agency_id' => $request->input('agency_id'),
            'title' => $request->input('title'),
            'type_price' => $typePercent,
        );
        $data = array_merge($array, $data);
        AgencyAgencyCategory::create($data);
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

}
