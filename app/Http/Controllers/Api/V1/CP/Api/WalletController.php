<?php

namespace App\Http\Controllers\Api\V1\CP\Api;

use App\ApiWallet;
use App\ApiWalletInvoice;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\CP\Api\Payment\ZarinpallController;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use Illuminate\Http\Request;
use App\Http\Requests;
use Morilog\Jalali\CalendarUtils;

class WalletController extends ApiController
{


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
        if ($request->input('start_date') && $request->input('end_date')) {
            $arrayStartDate = explode('/', $request->input('start_date'));
            $arrayEndDate = explode('/', $request->input('end_date'));
            $toDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayEndDate[0], $arrayEndDate[1], $arrayEndDate[2]);
            $fromDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayStartDate[0], $arrayStartDate[1], $arrayStartDate[2]);
            $toDate = date('Y-m-d', strtotime($toDate[0] . '-' . $toDate[1] . '-' . $toDate[2]));
            $fromDate = date('Y-m-d', strtotime($fromDate [0] . '-' . $fromDate [1] . '-' . $fromDate [2]));
        }
        $data["wallet"] = ApiWallet::where('api_id', $request->input('api_id'))->first();
        $data["walletInvoice"] = ApiWalletInvoice::where('api_id', $request->input('api_id'));
        if ($request->input('start_date') && $request->input('end_date'))
            $data["walletInvoice"] = $data["walletInvoice"]->where('created_at', '>', $fromDate)->where('created_at', '<=', $toDate);
        $data["walletInvoice"] = $data["walletInvoice"]->orderBy('created_at', 'desc')
            ->get()->map(function ($value) {
                $value->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->created_at));
            });
        return $this->respond($data);
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
        if (!$request->input('price'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن مبلغ اجباری می باشد.'
            );
        if (!$request->input('base_url'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن base_url اجباری می باشد.'
            );
        switch ($request->header('market')) {
            case Constants::MARKET_ZARINPAL:
                $zarinpal = new ZarinpallController();
                return $zarinpal->storePaymentWallet($request);
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


}
