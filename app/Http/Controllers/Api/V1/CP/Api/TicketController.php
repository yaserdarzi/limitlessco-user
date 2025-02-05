<?php

namespace App\Http\Controllers\Api\V1\CP\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Shopping;
use App\ShoppingInvoice;
use Illuminate\Http\Request;
use App\Http\Requests;
use Morilog\Jalali\CalendarUtils;

class TicketController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customer_id = Constants::SALES_TYPE_API . "-" . $request->input('api_id') . "-";
        $skip = 0;
        if ($request->input('page') != null)
            if ($request->input('page') != 0)
                $skip = 10 * $request->input('page');
        $shopping = Shopping::where('customer_id', 'like', "%{$customer_id}%");
        if ($request->input('search')) {
            $search = $request->input('search');
            $shopping = $shopping
                ->where('name', 'LIKE', "%$search%")
                ->orWhere('phone', 'LIKE', "%$search%");
        }
        $shopping = $shopping->take(10)->skip($skip)
            ->orderBy('created_at', 'desc')->get()->map(function ($value) {
                $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
                $value->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->created_at));
                return $value;
            });
        return $this->respond($shopping);
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
    public function show(Request $request)
    {
        $customer_id = Constants::SALES_TYPE_API . "-" . $request->input('api_id') . "-";
        $shopping = Shopping::where([
            'id' => $request->input('id'),
        ])->where('customer_id', 'like', "%{$customer_id}%")->first();
        if (!$shopping)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $shopping->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->date));
        $shopping->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->created_at));
        return $this->respond($shopping);
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

    public function ticketPaymentToken(Request $request)
    {
        $customer_id = Constants::SALES_TYPE_API . "-" . $request->input('api_id') . "-";
        $shoppingInvoice = ShoppingInvoice::where([
            'payment_token' => $request->input('payment_token'),
            ['customer_id', 'like', "%{$customer_id}%"]
        ])->first();
        if (!$shoppingInvoice)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $shopping = Shopping::where([
            'shopping_invoice_id' => $shoppingInvoice->id,
        ])->where('customer_id', 'like', "%{$customer_id}%")
            ->select(
                'voucher',
                'name',
                'phone',
                'title',
                'title_more',
                'date as check_in',
                'date_end as check_out',
                'count',
                'price_payment'
            )->first();
        if (!$shopping)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $shopping->check_in = CalendarUtils::strftime('Y-m-d', strtotime($shopping->check_in));
        if ($shopping->check_out)
            $shopping->check_out = CalendarUtils::strftime('Y-m-d', strtotime($shopping->check_out));
        $shopping->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->created_at));
        return $shopping;
    }

}
