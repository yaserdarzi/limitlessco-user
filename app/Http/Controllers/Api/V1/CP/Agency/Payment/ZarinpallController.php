<?php

namespace App\Http\Controllers\Api\V1\CP\Agency\Payment;

use App\AgencyWallet;
use App\AgencyWalletInvoice;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Shopping;
use App\ShoppingBag;
use App\ShoppingBagExpire;
use App\ShoppingInvoice;
use App\WalletInvoice;
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
        switch ($request->input('type')) {
            case "portal":
                return $this->portal($shoppingBag, $request, $data);
                break;
            case "wallet":
                if ($data['walletPayment'] != 0)
                    return $this->walletPortal($shoppingBag, $request, $data);
                else
                    return $this->wallet($shoppingBag, $request, $data);
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'plz check your type'
                );
        }
    }

    public function storePaymentWallet(Request $request)
    {
        $zarinPal = new Payment();
        $helper = new Helpers();
        $agency_id = $request->input('agency_id');
        $price = $helper->priceNumberDigitsToNormal($request->input('price'));
        $wallet = AgencyWallet::where('agency_id', $agency_id)->first();
        $walletPaymentTokenAgencyCount = AgencyWalletInvoice::count();
        $walletPaymentTokenAgency = "a-" . ++$walletPaymentTokenAgencyCount;
        $invoice = AgencyWalletInvoice::create([
            'agency_id' => $agency_id,
            'wallet_id' => $wallet->id,
            'price_before' => $wallet->price,
            'price' => $price,
            'price_after' => intval($wallet->price + $price),
            'price_all' => $price,
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_PENDING,
            'type' => Constants::INVOICE_TYPE_WALLET,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_WALLET . " - " . Constants::INVOICE_INVOICE_STATUS_INCREMENT,
            'payment_token' => $walletPaymentTokenAgency,
            'market' => Constants::INVOICE_MARKET_ZARINPAL,
            'info' => ['wallet' => $wallet],
        ]);
        // Doing the payment
        $payment = $zarinPal->request(
            intval($invoice->price),
            [
                'invoice' => $invoice->id
            ],
            route('api.cp.agency.wallet.callback')
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

    public function portal($shoppingBag, Request $request, $data)
    {
        $zarinPal = new Payment();
        $shoppingPaymentToken = $this->shoppingPaymentToken();
        $shoppingInvoice = ShoppingInvoice::create([
            'customer_id' => $data['customerId'],
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'count_all' => $data['countAll'],
            'price_all' => $data['priceAll'],
            'percent_all' => $data['percentAll'],
            'income_all_agency' => $data['incomeAgency'],
            'income_all_you' => $data['incomeYou'],
            'price_payment' => $data['pricePayment'],
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_PENDING,
            'type' => Constants::INVOICE_TYPE_SHOPPING,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING,
            'payment_token' => $shoppingPaymentToken,
            'market' => Constants::INVOICE_MARKET_ZARINPAL,
            'info' => $shoppingBag,
        ]);
        // Doing the payment
        $payment = $zarinPal->request(
            intval($shoppingInvoice->price_payment),
            [
                'shoppingInvoice' => $shoppingInvoice->id
            ],
            route('api.cp.agency.shopping.portal.callback')
        );
        if ($payment->get('result') == 'warning')
            throw new ApiException(ApiException::EXCEPTION_BAD_REQUEST_400, $payment->get('error'));
        return $this->respond(["url" => $payment->get('url')]);
    }

    public function portalCallback(Request $request)
    {
        $zarinPal = new Payment();
        $helper = new Helpers();
        // Verify the payment
        $authority = $request->input('Authority');
        $shoppingInvoice = ShoppingInvoice::find($request->input('shoppingInvoice'));
        $verify = $zarinPal->verify(intval($shoppingInvoice->price_payment), $authority);
        if ($verify->get('result') == 'success') {
            if ($shoppingInvoice->status == Constants::INVOICE_STATUS_PENDING) {
                $agency_id = explode('-', $shoppingInvoice->customer_id)[1];
                $wallet = AgencyWallet::where('agency_id', $agency_id)->first();
                $walletPaymentTokenAgencyCount = AgencyWalletInvoice::count();
                $walletPaymentTokenAgency = "a-" . ++$walletPaymentTokenAgencyCount;
                AgencyWalletInvoice::create([
                    'agency_id' => $agency_id,
                    'wallet_id' => $wallet->id,
                    'price_before' => $wallet->price,
                    'price' => 0,
                    'price_after' => $wallet->price,
                    'price_all' => $shoppingInvoice->price_payment,
                    'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
                    'status' => Constants::INVOICE_STATUS_SUCCESS,
                    'type' => Constants::INVOICE_TYPE_SHOPPING,
                    'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING,
                    'payment_token' => $walletPaymentTokenAgency,
                    'market' => Constants::INVOICE_MARKET_ZARINPAL,
                    'info' => ['wallet' => $wallet, 'zarinpal' => $verify],
                ]);
                foreach ($shoppingInvoice->info as $value) {
                    Shopping::create([
                        'shopping_id' => $value->shopping_id,
                        'customer_id' => $value->customer_id,
                        'shopping_invoice_id' => $shoppingInvoice->id,
                        'voucher' => $helper->voucher(explode('-', $value->shopping_id)[0]),
                        'name' => $shoppingInvoice->name,
                        'phone' => $shoppingInvoice->phone,
                        'title' => $value->title,
                        'title_more' => $value->title_more,
                        'date' => $value->date,
                        'date_end' => $value->date_end,
                        'start_hours' => $value->start_hours,
                        'end_hours' => $value->end_hours,
                        'price_fee' => $value->price_fee,
                        'percent_fee' => $value->percent_fee,
                        'count' => $value->count,
                        'price_all' => $value->price_all,
                        'percent_all' => $value->percent_all,
                        'income_agency' => $value->income_agency,
                        'income_you' => $value->income_you,
                        'price_payment' => ($value->price_all - $value->percent_all - $value->income_agency),
                        'status' => Constants::SHOPPING_STATUS_SUCCESS,
                        'shopping' => $value->shopping,
                    ]);
                }
                $shoppingInvoice->status = Constants::INVOICE_STATUS_SUCCESS;
                $shoppingInvoice->save();
                ShoppingBag::where(['customer_id' => $shoppingInvoice->customer_id])->delete();
                ShoppingBagExpire::where(['customer_id' => $shoppingInvoice->customer_id])->delete();
            }
//            return redirect('http://agency.justkish.com/success-message?token=' . $factorInvoice->payment_token . '&factor_id=' . $factor->id);
            return $this->respond(["status" => "success"]);
        } else {
            if ($shoppingInvoice->status == Constants::INVOICE_STATUS_PENDING) {
                $shoppingInvoice->status = Constants::INVOICE_STATUS_FAILED;
                $shoppingInvoice->save();
                ShoppingBagExpire::where(['customer_id' => $shoppingInvoice->customer_id])->update(["status" => Constants::SHOPPING_STATUS_SHOPPING]);
            }
//            return redirect('http://agency.justkish.com/failed-message?token=' . $factorInvoice->payment_token);
            return $this->respond(["status" => "failed"]);
        }
    }

    public function wallet($shoppingBag, Request $request, $data)
    {
        $helper = new Helpers();
        $shoppingPaymentToken = $this->shoppingPaymentToken();
        $shoppingInvoice = ShoppingInvoice::create([
            'customer_id' => $data['customerId'],
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'count_all' => $data['countAll'],
            'price_all' => $data['priceAll'],
            'percent_all' => $data['percentAll'],
            'income_all_agency' => $data['incomeAgency'],
            'income_all_you' => $data['incomeYou'],
            'price_payment' => $data['pricePayment'],
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_SUCCESS,
            'type' => Constants::INVOICE_TYPE_SHOPPING,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING,
            'payment_token' => $shoppingPaymentToken,
            'market' => Constants::INVOICE_MARKET_WALLET,
            'info' => $shoppingBag,
        ]);
        $agency_id = explode('-', $shoppingInvoice->customer_id)[1];
        $wallet = AgencyWallet::where('agency_id', $agency_id)->first();
        $walletPaymentTokenAgencyCount = AgencyWalletInvoice::count();
        $walletPaymentTokenAgency = "a-" . ++$walletPaymentTokenAgencyCount;
        AgencyWalletInvoice::create([
            'agency_id' => $agency_id,
            'wallet_id' => $wallet->id,
            'price_before' => $wallet->price,
            'price' => $data['pricePayment'],
            'price_after' => intval($wallet->price - $data['pricePayment']),
            'price_all' => $shoppingInvoice->price_payment,
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_SUCCESS,
            'type' => Constants::INVOICE_TYPE_SHOPPING,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING . " - " . Constants::INVOICE_INVOICE_STATUS_DECREMENT,
            'payment_token' => $walletPaymentTokenAgency,
            'market' => Constants::INVOICE_MARKET_WALLET,
            'info' => ['wallet' => $wallet],
        ]);
        AgencyWallet::where('agency_id', $agency_id)->update([
            'price' => intval($wallet->price - $data['pricePayment'])
        ]);
        foreach ($shoppingInvoice->info as $value) {
            Shopping::create([
                'shopping_id' => $value->shopping_id,
                'customer_id' => $value->customer_id,
                'shopping_invoice_id' => $shoppingInvoice->id,
                'voucher' => $helper->voucher(explode('-', $value->shopping_id)[0]),
                'name' => $shoppingInvoice->name,
                'phone' => $shoppingInvoice->phone,
                'title' => $value->title,
                'title_more' => $value->title_more,
                'date' => $value->date,
                'date_end' => $value->date_end,
                'start_hours' => $value->start_hours,
                'end_hours' => $value->end_hours,
                'price_fee' => $value->price_fee,
                'percent_fee' => $value->percent_fee,
                'count' => $value->count,
                'price_all' => $value->price_all,
                'percent_all' => $value->percent_all,
                'income_agency' => $value->income_agency,
                'income_you' => $value->income_you,
                'price_payment' => ($value->price_all - $value->percent_all - $value->income_agency),
                'status' => Constants::SHOPPING_STATUS_SUCCESS,
                'shopping' => $value->shopping,
            ]);
        }
        ShoppingBag::where(['customer_id' => $shoppingInvoice->customer_id])->delete();
        ShoppingBagExpire::where(['customer_id' => $shoppingInvoice->customer_id])->delete();
        return $this->respond(["url" => "success"]);
//        return redirect('http://agency.justkish.com/success-message?token=' . $factorInvoice->payment_token . '&factor_id=' . $factor->id);
    }

    public function walletPortal($shoppingBag, Request $request, $data)
    {
        $zarinPal = new Payment();
        $shoppingPaymentToken = $this->shoppingPaymentToken();
        $shoppingInvoice = ShoppingInvoice::create([
            'customer_id' => $data['customerId'],
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'count_all' => $data['countAll'],
            'price_all' => $data['priceAll'],
            'percent_all' => $data['percentAll'],
            'income_all_agency' => $data['incomeAgency'],
            'income_all_you' => $data['incomeYou'],
            'price_payment' => $data['pricePayment'],
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_PENDING,
            'type' => Constants::INVOICE_TYPE_SHOPPING,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING,
            'payment_token' => $shoppingPaymentToken,
            'market' => Constants::INVOICE_MARKET_ZARINPAL . " - " . Constants::INVOICE_MARKET_MELLAT,
            'info' => ["shopping" => $shoppingBag, "walletPayment" => $data['walletPayment']]
        ]);
        // Doing the payment
        $payment = $zarinPal->request(
            intval($shoppingInvoice->info->walletPayment),
            [
                'shoppingInvoice' => $shoppingInvoice->id
            ],
            route('api.cp.agency.shopping.wallet.portal.callback')
        );
        if ($payment->get('result') == 'warning')
            throw new ApiException(ApiException::EXCEPTION_BAD_REQUEST_400, $payment->get('error'));
        return $this->respond(["url" => $payment->get('url')]);
    }

    public function walletPortalCallback(Request $request)
    {
        $zarinPal = new Payment();
        $helper = new Helpers();
        // Verify the payment
        $authority = $request->input('Authority');
        $shoppingInvoice = ShoppingInvoice::find($request->input('shoppingInvoice'));
        $verify = $zarinPal->verify(intval($shoppingInvoice->info->walletPayment), $authority);
        if ($verify->get('result') == 'success') {
            if ($shoppingInvoice->status == Constants::INVOICE_STATUS_PENDING) {
                $agency_id = explode('-', $shoppingInvoice->customer_id)[1];
                $wallet = AgencyWallet::where('agency_id', $agency_id)->first();
                $walletPaymentTokenAgencyCount = AgencyWalletInvoice::count();
                $walletPaymentTokenAgency = "a-" . ++$walletPaymentTokenAgencyCount;
                AgencyWalletInvoice::create([
                    'agency_id' => $agency_id,
                    'wallet_id' => $wallet->id,
                    'price_before' => $wallet->price,
                    'price' => $shoppingInvoice->info->walletPayment,
                    'price_after' => intval($wallet->price + $shoppingInvoice->info->walletPayment),
                    'price_all' => $shoppingInvoice->price_payment,
                    'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
                    'status' => Constants::INVOICE_STATUS_SUCCESS,
                    'type' => Constants::INVOICE_TYPE_SHOPPING,
                    'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING . " - " . Constants::INVOICE_INVOICE_STATUS_INCREMENT,
                    'payment_token' => $walletPaymentTokenAgency,
                    'market' => Constants::INVOICE_MARKET_ZARINPAL,
                    'info' => ['wallet' => $wallet, 'zarinpal' => $verify],
                ]);
                $walletPaymentTokenAgencyCount = AgencyWalletInvoice::count();
                $walletPaymentTokenAgency = "a-" . ++$walletPaymentTokenAgencyCount;
                AgencyWalletInvoice::create([
                    'agency_id' => $agency_id,
                    'wallet_id' => $wallet->id,
                    'price_before' => intval($wallet->price + $shoppingInvoice->info->walletPayment),
                    'price' => $shoppingInvoice->price_payment,
                    'price_after' => 0,
                    'price_all' => $shoppingInvoice->price_payment,
                    'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
                    'status' => Constants::INVOICE_STATUS_SUCCESS,
                    'type' => Constants::INVOICE_TYPE_SHOPPING,
                    'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING . " - " . Constants::INVOICE_INVOICE_STATUS_DECREMENT,
                    'payment_token' => $walletPaymentTokenAgency,
                    'market' => Constants::INVOICE_MARKET_WALLET,
                    'info' => ['wallet' => $wallet],
                ]);
                AgencyWallet::where('agency_id', $agency_id)->update([
                    'price' => 0
                ]);
                foreach ($shoppingInvoice->info->shopping as $value) {
                    Shopping::create([
                        'shopping_id' => $value->shopping_id,
                        'customer_id' => $value->customer_id,
                        'shopping_invoice_id' => $shoppingInvoice->id,
                        'voucher' => $helper->voucher(explode('-', $value->shopping_id)[0]),
                        'name' => $shoppingInvoice->name,
                        'phone' => $shoppingInvoice->phone,
                        'title' => $value->title,
                        'title_more' => $value->title_more,
                        'date' => $value->date,
                        'date_end' => $value->date_end,
                        'start_hours' => $value->start_hours,
                        'end_hours' => $value->end_hours,
                        'price_fee' => $value->price_fee,
                        'percent_fee' => $value->percent_fee,
                        'count' => $value->count,
                        'price_all' => $value->price_all,
                        'percent_all' => $value->percent_all,
                        'income_agency' => $value->income_agency,
                        'income_you' => $value->income_you,
                        'price_payment' => ($value->price_all - $value->percent_all - $value->income_agency),
                        'status' => Constants::SHOPPING_STATUS_SUCCESS,
                        'shopping' => $value->shopping,
                    ]);
                }
                $shoppingInvoice->status = Constants::INVOICE_STATUS_SUCCESS;
                $shoppingInvoice->save();
                ShoppingBag::where(['customer_id' => $shoppingInvoice->customer_id])->delete();
                ShoppingBagExpire::where(['customer_id' => $shoppingInvoice->customer_id])->delete();
            }
//            return redirect('http://agency.justkish.com/success-message?token=' . $factorInvoice->payment_token . '&factor_id=' . $factor->id);
            return $this->respond(["status" => "success"]);
        } else {
            if ($shoppingInvoice->status == Constants::INVOICE_STATUS_PENDING) {
                $shoppingInvoice->status = Constants::INVOICE_STATUS_FAILED;
                $shoppingInvoice->save();
                ShoppingBagExpire::where(['customer_id' => $shoppingInvoice->customer_id])->update(["status" => Constants::SHOPPING_STATUS_SHOPPING]);
            }
//            return redirect('http://agency.justkish.com/failed-message?token=' . $factorInvoice->payment_token);
            return $this->respond(["status" => "failed"]);
        }
    }

    public function walletCallback(Request $request)
    {
        $zarinPal = new Payment();
        // Verify the payment
        $authority = $request->input('Authority');
        $invoice = AgencyWalletInvoice::find($request->input('invoice'));
        $verify = $zarinPal->verify(intval($invoice->price), $authority);
        if ($verify->get('result') == 'success') {
            if ($invoice->status == Constants::INVOICE_STATUS_PENDING) {
                $agency_id = $invoice->agency_id;
                AgencyWallet::where('agency_id', $agency_id)->update([
                    'price' => $invoice->price_after
                ]);
                $invoice->status = Constants::INVOICE_STATUS_SUCCESS;
                $invoice->save();
            }
//            return redirect('http://agency.justkish.com/success-message?token=' . $factorInvoice->payment_token . '&factor_id=' . $factor->id);
            return $this->respond(["status" => "success"]);
        } else {
            if ($invoice->status == Constants::INVOICE_STATUS_PENDING) {
                $invoice->status = Constants::INVOICE_STATUS_FAILED;
                $invoice->save();
            }
//            return redirect('http://agency.justkish.com/failed-message?token=' . $factorInvoice->payment_token);
            return $this->respond(["status" => "failed"]);
        }
    }


    /////////////////////////private function/////////////////////////

    private function shoppingPaymentToken()
    {
        $shoppingInvoice = ShoppingInvoice::count();
        return "s-" . ++$shoppingInvoice;
    }

}
