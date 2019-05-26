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
        $priceAll = 0;
        $percentAll = 0;
        $shoppingBag = ShoppingBag::where('customer_id', $customer_id)->get();
        if (!sizeof($shoppingBag))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، سبد خرید شما خالی می باشد.'
            );
        foreach ($shoppingBag as $value) {
            $priceAll = $priceAll + $value->price_all;
            $percentAll = $percentAll + $value->percent_all;
            $incomeAgency = $incomeAgency + $value->income_agency;
        }
        $pricePayment = $priceAll - $percentAll - $incomeAgency;
        $wallet = AgencyWallet::where(['agency_id' => $request->input('agency_id')])->first();
        if ($wallet->price <= $pricePayment)
            $walletPayment = $pricePayment - $wallet->price;
        else
            $walletPayment = $wallet->price - $pricePayment;
        return $this->respond([
            'pricePayment' => $pricePayment,
            'walletPrice' => $wallet->price,
            'walletPayment' => $walletPayment
        ]);
    }
}
