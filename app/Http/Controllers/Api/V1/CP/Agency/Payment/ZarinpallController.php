<?php

namespace App\Http\Controllers\Api\V1\CP\Agency\Payment;

use App\AgencyWallet;
use App\AgencyWalletInvoice;
use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\AgencyApp;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Shopping;
use App\ShoppingBag;
use App\ShoppingBagExpire;
use App\ShoppingInvoice;
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
//                return $zarinpal->store($request, $data);
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'plz check your type'
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

    public function portal($shoppingBag, Request $request, $data)
    {
        $zarinPal = new Payment();
        $shoppingPaymentToken = $this->shoppingPaymentToken($request->input('app_title'));
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
                $walletPaymentTokenAgency = "a-" . '-' . ++$walletPaymentTokenAgencyCount;
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


    /////////////////////////private function/////////////////////////

    private function shoppingPaymentToken($appTitle)
    {
        $shoppingInvoice = ShoppingInvoice::count();
        return "s-" . substr($appTitle, 0, 1) . '-' . ++$shoppingInvoice;
    }

}
