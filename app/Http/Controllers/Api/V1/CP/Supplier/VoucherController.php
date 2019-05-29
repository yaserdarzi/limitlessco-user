<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Shopping;
use Illuminate\Http\Request;
use App\Http\Requests;

class VoucherController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->input('voucher'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی وارد کردن واچر اجباری می باشد."
            );
        $shopping = Shopping::where([
            'voucher' => $request->input('voucher'),
        ])->first();
        if (!$shopping)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی لطفا واچر خود را بررسی نمایید."
            );
        switch (explode('-', $shopping->shopping_id)[0]) {
            case Constants::APP_NAME_HOTEL:
                return $this->respond($this->hotelCheck($shopping, $request));
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    "کاربر گرامی لطفا واچر خود را بررسی نمایید."
                );
        }
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
        if (!$request->input('voucher'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی وارد کردن واچر اجباری می باشد."
            );
        $shopping = Shopping::where([
            'voucher' => $request->input('voucher'),
        ])->first();
        if (!$shopping)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی لطفا واچر خود را بررسی نمایید."
            );
        switch (explode('-', $shopping->shopping_id)[0]) {
            case Constants::APP_NAME_HOTEL:
                $this->hotelCheck($shopping, $request);
                return $this->respond($this->revoke($shopping));
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    "کاربر گرامی لطفا واچر خود را بررسی نمایید."
                );
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


    private function hotelCheck($shopping, Request $request)
    {
        if (!in_array($shopping->shopping->hotel->app_id, $request->input('apps_id')))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        foreach ($shopping->shopping->roomEpisode as $value)
            if ($value->supplier_id != $request->input('supplier_id'))
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی شما واچر مورد نظر برای شما نمی باشد.'
                );
        return $shopping;
    }

    private function revoke($shopping)
    {
        if ($shopping->status != Constants::SHOPPING_STATUS_SUCCESS)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی واچر مورد نظر شما قبلا استفاده شده است.'
            );
        Shopping::where('id', $shopping->id)->update(['status' => Constants::SHOPPING_STATUS_FINISH]);
        return ["status" => "success"];
    }
}
