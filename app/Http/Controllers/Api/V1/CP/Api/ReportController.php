<?php

namespace App\Http\Controllers\Api\V1\CP\Api;

use App\Api;
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
        $data['incomeAllApi'] = 0;
        $data['countAll'] = 0;
        if ($request->input('role') == Constants::ROLE_ADMIN) {
            $customer_id = Constants::SALES_TYPE_API . "-" . $request->input('api_id') . "-";
            $data['shoppingInvoice'] = ShoppingInvoice::where('customer_id', 'like', "%{$customer_id}%")
                ->get();
            foreach ($data['shoppingInvoice'] as $value) {
                $value->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->created_at));
                $data['countAll'] = $data['countAll'] + $value->count_all;
                $data['incomeAllApi'] = $data['incomeAllApi'] + $value->income_all_api;
            }
        }
        return $this->respond($data);
    }

    public function chart(Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $customer_id = Constants::SALES_TYPE_API . "-" . $request->input('api_id') . "-";
        $first = Jalalian::forge(strtotime('now'))->format('%Y/%m/%d');
        for ($i = 0; $i <= 6; $i++) {
            $firstNowTime = date('Y/m/d 00:00:00', strtotime("-" . $i . " days", strtotime($first)));
            $lastNowTime = date('Y/m/d 23:59:59', strtotime("-" . $i . " days", strtotime($first)));
            $dateTimeFirst = \Morilog\Jalali\CalendarUtils::createDatetimeFromFormat('Y/m/d 00:00:00', $firstNowTime);
            $dateTimeLast = \Morilog\Jalali\CalendarUtils::createDatetimeFromFormat('Y/m/d 23:59:59', $lastNowTime);
            $data['month'][$i] = Jalalian::forge($dateTimeFirst->getTimestamp())->format('%A');
            $data['price'][$i] = 0;
            $shopping = Shopping::whereBetween('created_at', [$dateTimeFirst->format('Y-m-d 00:00:00'), $dateTimeLast->format('Y-m-d 23:59:59')])
                ->where('customer_id', 'like', "%{$customer_id}%")->get();
            foreach ($shopping as $value)
                $data['price'][$i] = $data['price'][$i] + $value->price_all;
        }
        return $this->respond($data);
    }
}
