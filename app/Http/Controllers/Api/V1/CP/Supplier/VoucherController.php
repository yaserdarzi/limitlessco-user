<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Shopping;
use App\Supplier;
use App\SupplierWallet;
use App\SupplierWalletInvoice;
use Illuminate\Http\Request;
use App\Http\Requests;
use Morilog\Jalali\CalendarUtils;

class VoucherController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->input('voucher'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی وارد کردن واچر اجباری می باشد."
            );
        $shopping = Shopping::where([
            'voucher' => $request->input('voucher'),
        ])->first();
        if (!$shopping)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی لطفا واچر خود را بررسی نمایید."
            );
        switch (explode('-', $shopping->shopping_id)[0]) {
            case Constants::APP_NAME_HOTEL:
                return $this->respond($this->hotelCheck($shopping, $request));
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    "کاربر گرامی لطفا واچر خود را بررسی نمایید."
                );
        }
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
        if (!$request->input('voucher'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی وارد کردن واچر اجباری می باشد."
            );
        $shopping = Shopping::where([
            'voucher' => $request->input('voucher'),
        ])->first();
        if (!$shopping)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی لطفا واچر خود را بررسی نمایید."
            );
        switch (explode('-', $shopping->shopping_id)[0]) {
            case Constants::APP_NAME_HOTEL:
                $this->hotelCheck($shopping, $request);
                return $this->respond($this->revoke($shopping));
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    "کاربر گرامی لطفا واچر خود را بررسی نمایید."
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


    ///////////////////private function///////////////////////


    private function hotelCheck($shopping, Request $request)
    {
        if (!in_array($shopping->shopping->hotel->app_id, $request->input('apps_id')))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        foreach ($shopping->shopping->roomEpisode as $value)
            if ($value->supplier_id != $request->input('supplier_id'))
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی شما واچر مورد نظر برای شما نمی باشد.'
                );
        $shopping->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->date));
        $shopping->date_end_persian = null;
        $shopping->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->created_at));
        if ($shopping->date_end)
            $shopping->date_end_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->date_end));
        return $shopping;
    }

    private function revoke($shopping)
    {
        if ($shopping->status != Constants::SHOPPING_STATUS_SUCCESS)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی واچر مورد نظر شما قبلا استفاده شده است.'
            );
        $supplier = Supplier::where(['id' => $shopping->supplier_id])->first();
        $incomeSupplier = 0;
        if ($supplier->type == Constants::TYPE_PRICE) {
            $incomeSupplier = $shopping->price_payment - $supplier->price;
        } elseif ($supplier->type == Constants::TYPE_PERCENT) {
            if ($supplier->percent < 100) {
                $floatPercent = floatval("0." . $supplier->percent);
                $incomeSupplier = $shopping->price_payment - ($shopping->price_payment * $floatPercent);
            }
        }
        $wallet = SupplierWallet::where('supplier_id', $supplier->id)->first();
        $walletPaymentTokenSupplierCount = SupplierWalletInvoice::count();
        $walletPaymentTokenSupplier = "SW-" . ++$walletPaymentTokenSupplierCount;
        SupplierWalletInvoice::create([
            'supplier_id' => $supplier->id,
            'wallet_id' => $wallet->id,
            'price_before' => $supplier->income,
            'price' => $incomeSupplier,
            'price_after' => intval($supplier->income + $incomeSupplier),
            'price_all' => $incomeSupplier,
            'type_status' => Constants::INVOICE_TYPE_STATUS_INCOME,
            'status' => Constants::INVOICE_STATUS_SUCCESS,
            'type' => Constants::INVOICE_TYPE_INCOME_SUPPLIER,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_INCOME_SUPPLIER . " - " . Constants::INVOICE_INVOICE_STATUS_INCREMENT,
            'payment_token' => $walletPaymentTokenSupplier,
            'market' => Constants::INVOICE_MARKET_INCOME_SUPPLIER,
            'info' => ['wallet' => $wallet, "shopping" => $shopping],
        ]);
        Supplier::where('id', $supplier->id)->update([
            'income' => intval($supplier->income + $incomeSupplier)
        ]);
        Shopping::where('id', $shopping->id)->update(['status' => Constants::SHOPPING_STATUS_FINISH]);
        return ["status" => "success"];
    }
}
