<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\AgencyWallet;
use App\AgencyWalletInvoice;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\CP\Agency\Payment\ZarinpallController;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use Illuminate\Http\Request;
use App\Http\Requests;

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
        $data["wallet"] = AgencyWallet::where('agency_id', $request->input('agency_id'))->first();
        $data["walletInvoice"] = AgencyWalletInvoice::where('agency_id', $request->input('agency_id'));
        if ($request->input('start_date') && $request->input('end_date'))
            $data["walletInvoice"] = $data["walletInvoice"]->where('created_at', '>', $fromDate)->where('created_at', '<=', $toDate);
        $data["walletInvoice"] = $data["walletInvoice"]->orderBy('created_at', 'desc')->get();
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

    public function agencyWalletCreditSharjCallback(Request $request)
    {
        // Verify the payment
        $authority = $request->input('Authority');
        $verify = $this->zarinPal->verify($request->input('price'), $authority);
        $invoiceAgencyWalletCreditInfo = InvoiceAgencyWalletCredit::find($request->input('invoiceAgencyWalletCreditInfo'));
        if ($verify->get('result') == 'success') {
            if ($invoiceAgencyWalletCreditInfo->status == Constants::INVOICE_WALLET_CREDIT_STATUS_PENDING) {
                $invoiceAgencyWalletCreditInfo->status = Constants::INVOICE_WALLET_CREDIT_STATUS_SUCCESS;
                $invoiceAgencyWalletCreditInfo->invoice_status = Constants::INVOICE_WALLET_CREDIT_STATUS_SUCCESS;
                $invoiceAgencyWalletCreditInfo->info = ["zarinpal" => $verify];
                $invoiceAgencyWalletCreditInfo->save();
                $price = intval($request->input('price'));
                $wallet = AgencyWalletCredit::where('agency_agent_id', $request->input("agency_agent_id"))->first();
                AgencyWalletCredit::where('agency_agent_id', $request->input("agency_agent_id"))->update([
                    'wallet_price' => intval($wallet->wallet_price + $price)
                ]);
            }
            return redirect('http://agency.justkish.com/success-message?token=' . $invoiceAgencyWalletCreditInfo->payment_token);
        } else {
            if ($invoiceAgencyWalletCreditInfo->status == Constants::INVOICE_WALLET_CREDIT_STATUS_PENDING) {
                $invoiceAgencyWalletCreditInfo->status = Constants::INVOICE_WALLET_CREDIT_STATUS_FAILED;
                $invoiceAgencyWalletCreditInfo->invoice_status = Constants::INVOICE_WALLET_CREDIT_STATUS_FAILED;
                $invoiceAgencyWalletCreditInfo->info = ["zarinpal" => $verify];
                $invoiceAgencyWalletCreditInfo->save();
            }
            return redirect('http://agency.justkish.com/failed-message?token=' . $invoiceAgencyWalletCreditInfo->payment_token);
        }
    }
}
