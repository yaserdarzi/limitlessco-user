<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Shopping;
use App\ShoppingInvoice;
use Illuminate\Http\Request;
use App\Http\Requests;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class ReportController extends ApiController
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

    public function sales(Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('from'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن تاریخ شروع اجباری می باشد.'
            );
        if (!$request->input('to'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن تاریخ پایان اجباری می باشد.'
            );
        $arrayStartDate = explode('/', $request->input('from'));
        $arrayEndDate = explode('/', $request->input('to'));
        $toDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayEndDate[0], $arrayEndDate[1], $arrayEndDate[2]);
        $fromDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayStartDate[0], $arrayStartDate[1], $arrayStartDate[2]);
        $toDate = date('Y-m-d', strtotime($toDate[0] . '-' . $toDate[1] . '-' . $toDate[2]));
        $fromDate = date('Y-m-d', strtotime($fromDate [0] . '-' . $fromDate [1] . '-' . $fromDate [2]));
        $data['countAll'] = 0;
        $data['shoppingInvoice'] = ShoppingInvoice::whereBetween(
            'created_at', [$fromDate, $toDate]
        )->get();
        foreach ($data['shoppingInvoice'] as $key => $val) {
            foreach ($val->info->shopping as $item=> $value) {
                $status = true;
                switch (explode('-', $value->shopping_id)[0]) {
                    case Constants::APP_NAME_HOTEL:
                        $status = $this->hotelCheck($value, $request);
                        if (!$status)
                            unset($data['shoppingInvoice'][$key]);
                        break;
                }
                if ($status) {
                    $val->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($val->created_at));
                    $data['countAll'] = $data['countAll'] + $val->count_all;
                }
            }
        }
        return $this->respond($data);
    }

    ///////////////////private function///////////////////////

    private function hotelCheck($shopping, Request $request)
    {
        if (!in_array($shopping->shopping->hotel->app_id, $request->input('apps_id')))
            return false;
        foreach ($shopping->shopping->roomEpisode as $value)
            if ($value->supplier_id != $request->input('supplier_id'))
                return false;
        $shopping->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->date));
        $shopping->date_end_persian = null;
        $shopping->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->created_at));
        if ($shopping->date_end)
            $shopping->date_end_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->date_end));
        return true;
    }


}
