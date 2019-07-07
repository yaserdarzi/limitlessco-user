<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier\Payment;

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
use Illuminate\Support\Facades\DB;
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
        return $this->portal($shoppingBag, $request, $data);
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
            'income_all' => $data['incomeAgency'],
            'income_you' => $data['incomeYou'],
            'price_payment' => $data['pricePayment'],
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_PENDING,
            'type' => Constants::INVOICE_TYPE_SHOPPING,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING,
            'payment_token' => $shoppingPaymentToken,
            'market' => Constants::INVOICE_MARKET_ZARINPAL,
            'info' => ["shopping" => $shoppingBag, "base_url" => $request->input('base_url')]
        ]);
        // Doing the payment
        $payment = $zarinPal->request(
            intval($shoppingInvoice->price_payment),
            [
                'shoppingInvoice' => $shoppingInvoice->id
            ],
            route('api.cp.supplier.shopping.portal.callback')
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
                foreach ($shoppingInvoice->info->shopping as $value) {
                    Shopping::create([
                        'shopping_id' => $value->shopping_id,
                        'customer_id' => $value->customer_id,
                        'supplier_id' => $value->shopping->roomEpisode[0]->supplier_id,
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
                        'price_income' => $value->price_income,
                        'percent_fee' => $value->percent_fee,
                        'count' => $value->count,
                        'price_all' => $value->price_all,
                        'percent_all' => $value->percent_all,
                        'income_all' => $value->income_all,
                        'income_you' => $value->income_you,
                        'price_payment' => ($value->price_all - $value->percent_all - $value->income_all),
                        'status' => Constants::SHOPPING_STATUS_SUCCESS,
                        'shopping' => $value->shopping,
                    ]);
                }
                $shoppingInvoice->status = Constants::INVOICE_STATUS_SUCCESS;
                $shoppingInvoice->save();
                ShoppingBag::where(['customer_id' => $shoppingInvoice->customer_id])->delete();
                ShoppingBagExpire::where(['customer_id' => $shoppingInvoice->customer_id])->delete();
            }
            return redirect($shoppingInvoice->info->base_url . '/success?token=' . $shoppingInvoice->payment_token);
        } else {
            if ($shoppingInvoice->status == Constants::INVOICE_STATUS_PENDING) {
                $shoppingInvoice->status = Constants::INVOICE_STATUS_FAILED;
                $shoppingInvoice->save();
                ShoppingBagExpire::where(['customer_id' => $shoppingInvoice->customer_id])->update(["status" => Constants::SHOPPING_STATUS_SHOPPING]);
                $this->expireShopping($shoppingInvoice->customer_id);
            }
            return redirect($shoppingInvoice->info->base_url . '/failed?token=' . $shoppingInvoice->payment_token);
        }
    }


    /////////////////////////private function/////////////////////////

    private function shoppingPaymentToken()
    {
        $shoppingInvoice = ShoppingInvoice::count();
        return "s-" . ++$shoppingInvoice;
    }


    private function expireShopping($customer_id)
    {
        $shoppingBagExpire = ShoppingBagExpire::where([
            'customer_id' => $customer_id
        ])->first();
        if ($shoppingBagExpire) {
            ShoppingBagExpire::where([
                'id' => $shoppingBagExpire->id
            ])->update(['status' => Constants::SHOPPING_STATUS_DELETE]);
            $shoppingBag = ShoppingBag::where([
                'customer_id' => $shoppingBagExpire->customer_id
            ])->get();
            if (sizeof($shoppingBag))
                foreach ($shoppingBag as $value)
                    switch (explode('-', $value->shopping_id)[0]) {
                        case Constants::APP_NAME_HOTEL:
                            $this->hotelCheck($value);
                            $this->deleteShoppingBagExpire($shoppingBagExpire->id);
                            break;
                    }
        }
    }

    private function deleteShoppingBagExpire($id)
    {
        ShoppingBagExpire::where([
            'id' => $id
        ])->delete();
    }

    private function hotelCheck($shoppingBag)
    {
        foreach ($shoppingBag->shopping->roomEpisode as $value) {
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->decrement('capacity_filled', $shoppingBag->count);
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->increment('capacity_remaining', $shoppingBag->count);
        }
        ShoppingBag::where('id', $shoppingBag->id)->delete();
    }

}
