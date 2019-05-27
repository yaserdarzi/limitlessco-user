<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\AgencyWallet;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\CP\Agency\Payment\ZarinpallController;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\ShoppingBag;
use App\ShoppingBagExpire;
use Illuminate\Http\Request;
use App\Http\Requests;

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
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام و نام خانوادگی اجباری می باشد.'
            );
        if (!$request->input('phone'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره همراه اجباری می باشد.'
            );
        $customer_id = Constants::AGENCY_DB . "-" . $request->input('agency_id') . "-" . $request->input('user_id');
        $incomeAgency = 0;
        $incomeYou = 0;
        $priceAll = 0;
        $countAll = 0;
        $percentAll = 0;
        $shoppingBag = ShoppingBag::where('customer_id', $customer_id)->get();
        if (!sizeof($shoppingBag))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، سبد خرید شما خالی می باشد.'
            );
        foreach ($shoppingBag as $value) {
            $priceAll = $priceAll + $value->price_all;
            $countAll = $countAll + $value->count;
            $percentAll = $percentAll + $value->percent_all;
            $incomeAgency = $incomeAgency + $value->income_agency;
            $incomeYou = $incomeYou + $value->income_you;
        }
        ShoppingBagExpire::where([
            'customer_id' => $customer_id
        ])->update(['status' => Constants::SHOPPING_STATUS_PAYMENT]);
        $pricePayment = $priceAll - $percentAll - $incomeAgency;
        $wallet = AgencyWallet::where(['agency_id' => $request->input('agency_id')])->first();
        if ($wallet->price <= $pricePayment)
            $walletPayment = $pricePayment - $wallet->price;
        else
            $walletPayment = 0;
        $data = [
            'priceAll' => $priceAll,
            'percentAll' => $percentAll,
            'pricePayment' => $pricePayment,
            'walletPrice' => $wallet->price,
            'walletPayment' => $walletPayment,
            'countAll' => $countAll,
            'incomeAgency' => $incomeAgency,
            'incomeYou' => $incomeYou,
            'customerId' => $customer_id
        ];
        switch ($request->header('market')) {
            case Constants::MARKET_ZARINPAL:
                $zarinpal = new ZarinpallController();
                return $zarinpal->store($shoppingBag, $request, $data);
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'plz check your market'
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
            $walletPayment = 0;
        return $this->respond([
            'pricePayment' => $pricePayment,
            'walletPrice' => $wallet->price,
            'walletPayment' => $walletPayment
        ]);
    }
}
