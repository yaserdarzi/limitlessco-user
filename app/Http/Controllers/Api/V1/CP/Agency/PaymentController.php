<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\AgencyWallet;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\ShoppingBag;
use App\ShoppingBagExpire;
use Illuminate\Http\Request;
use App\Http\Requests;
use Morilog\Jalali\CalendarUtils;

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


    public function checkout(Request $request)
    {
        $customer_id = Constants::AGENCY_DB . "-" . $request->input('agency_id') . "-" . $request->input('user_id');
        $incomeAgency = 0;
        $incomeYou = 0;
        $priceAll = 0;
        $percentAll = 0;
        $shoppingBag = ShoppingBag::where('customer_id', $customer_id)->get();
        if (!sizeof($shoppingBag))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، سبد خرید شما خالی می باشد.'
            );
        ShoppingBagExpire::where([
            'customer_id' => $customer_id,
            'status' => Constants::SHOPPING_STATUS_SHOPPING
        ])->first();
        foreach ($shoppingBag as $value) {
            $priceAll = $priceAll + $value->price_all;
            $percentAll = $percentAll + $value->percent_all;
            $incomeAgency = $incomeAgency + $value->income_agency;
            $incomeYou = $incomeYou + $value->income_you;
            $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
            $value->date_end_persian = null;
            if ($value->date_end)
                $value->date_end_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date_end));
        }
        $pricePayment = $priceAll - $percentAll - $incomeAgency;
        $wallet = AgencyWallet::where(['agency_id' => $request->input('agency_id')])->first();
        dd($shoppingBag);
        dd($request->all(), $wallet);

    }
}
