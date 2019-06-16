<?php

namespace App\Http\Controllers\Api\V1\CP\Crm;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Shopping;
use App\Supplier;
use App\SupplierWallet;
use App\SupplierWalletInvoice;
use Illuminate\Http\Request;

class CheckoutController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

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
        if (!$request->input('price'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن مبلغ اجباری می باشد.'
            );
        if (!$request->input('ref'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره پیگیری اجباری می باشد.'
            );
        if (!$request->input('supplier_id'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن عرضه کننده اجباری می باشد.'
            );
        if (!$supplier = Supplier::where(['id' => $request->input('supplier_id')])->first())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن عرضه کننده اجباری می باشد.'
            );
        if (!SupplierWallet::where(['id' => $request->input('supplier_id')])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن عرضه کننده اجباری می باشد.'
            );
        if ($supplier->income < $request->input('price'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، مبلع مورد نظر کمتر از موجودی عرضه کننده می باشد.'
            );
        $wallet = SupplierWallet::where('supplier_id', $supplier->id)->first();
        $price = intval($this->help->priceNumberDigitsToNormal($request->input('price')));
        $walletPaymentTokenSupplierCount = SupplierWalletInvoice::count();
        $walletPaymentTokenSupplier = "SW-" . ++$walletPaymentTokenSupplierCount;
        SupplierWalletInvoice::create([
            'supplier_id' => $supplier->id,
            'wallet_id' => $wallet->id,
            'price_before' => $supplier->income,
            'price' => $price,
            'price_after' => intval($supplier->income - $price),
            'price_all' => $price,
            'type_status' => Constants::INVOICE_TYPE_STATUS_INCOME_DECREMENT,
            'status' => Constants::INVOICE_STATUS_SUCCESS,
            'type' => Constants::INVOICE_TYPE_INCOME_DECREMENT_SUPPLIER,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_INCOME_DECREMENT_SUPPLIER . " - " . Constants::INVOICE_INVOICE_STATUS_WITHDRAW,
            'payment_token' => $walletPaymentTokenSupplier,
            'ref_id' => $request->input('ref'),
            'market' => Constants::INVOICE_MARKET_INCOME_SUPPLIER,
            'info' => ['wallet' => $wallet],
        ]);
        Supplier::where('id', $supplier->id)->update([
            'income' => intval($supplier->income - $price)
        ]);
        return $this->respond(["status" => "success"]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
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
}
