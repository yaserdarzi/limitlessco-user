<?php

namespace App\Http\Controllers\Api\V1\CP\Api\Payment;

use App\ApiWallet;
use App\ApiWalletInvoice;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Rasulian\ZarinPal\Payment;

class ZarinpallController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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
    public function store($shoppingBag, Request $request, $data)
    {

    }

    public function storePaymentWallet(Request $request)
    {
        $zarinPal = new Payment();
        $helper = new Helpers();
        $api_id = $request->input('api_id');
        $price = $helper->priceNumberDigitsToNormal($request->input('price'));
        $wallet = ApiWallet::where('api_id', $api_id)->first();
        $walletPaymentTokenApiCount = ApiWalletInvoice::count();
        $walletPaymentTokenApi = "a-" . ++$walletPaymentTokenApiCount;
        $invoice = ApiWalletInvoice::create([
            'api_id' => $api_id,
            'wallet_id' => $wallet->id,
            'price_before' => $wallet->price,
            'price' => $price,
            'price_after' => intval($wallet->price + $price),
            'price_all' => $price,
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_PENDING,
            'type' => Constants::INVOICE_TYPE_WALLET,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_WALLET . " - " . Constants::INVOICE_INVOICE_STATUS_INCREMENT,
            'payment_token' => $walletPaymentTokenApi,
            'market' => Constants::INVOICE_MARKET_ZARINPAL,
            'info' => ['wallet' => $wallet, "base_url" => $request->input('base_url')],
        ]);
        // Doing the payment
        $payment = $zarinPal->request(
            intval($invoice->price),
            [
                'invoice' => $invoice->id
            ],
            route('api.cp.api.wallet.callback')
        );
        if ($payment->get('result') == 'warning')
            throw new ApiException(ApiException::EXCEPTION_BAD_REQUEST_400, $payment->get('error'));
        return $this->respond(["url" => $payment->get('url')]);
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


    public function walletCallback(Request $request)
    {
        $zarinPal = new Payment();
        // Verify the payment
        $authority = $request->input('Authority');
        $invoice = ApiWalletInvoice::find($request->input('invoice'));
        $verify = $zarinPal->verify(intval($invoice->price), $authority);
        if ($verify->get('result') == 'success') {
            if ($invoice->status == Constants::INVOICE_STATUS_PENDING) {
                $api_id = $invoice->api_id;
                ApiWallet::where('api_id', $api_id)->update([
                    'price' => $invoice->price_after
                ]);
                $invoice->status = Constants::INVOICE_STATUS_SUCCESS;
                $invoice->save();
            }
            return redirect($invoice->info->base_url . '/success?token=' . $invoice->payment_token);
        } else {
            if ($invoice->status == Constants::INVOICE_STATUS_PENDING) {
                $invoice->status = Constants::INVOICE_STATUS_FAILED;
                $invoice->save();
            }
            return redirect($invoice->info->base_url . '/failed?token=' . $invoice->payment_token);
        }
    }


    /////////////////////////private function/////////////////////////

}
