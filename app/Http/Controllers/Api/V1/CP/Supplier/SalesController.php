<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Sales;
use App\SupplierSales;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class SalesController extends ApiController
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
        $sales = Sales::select(
            '*',
            DB::raw("CASE WHEN logo != '' THEN (concat ( '" . url('') . "/files/sales/', logo) ) ELSE '" . url('/files/sales/default.svg') . "' END as logo")
        )->get();
        foreach ($sales as $value) {
            $supplier = SupplierSales::where([
                'supplier_id' => $request->input('supplier_id'),
                'sales_id' => $value->id
            ])->first();
            if (!$supplier)
                $supplier = [
                    'supplier_id' => $request->input('supplier_id'),
                    'sales_id' => $value->id,
                    'capacity_percent' => 0,
                    'type_price' => Constants::TYPE_PERCENT,
                    'price' => 0,
                    'percent' => 0,
                    'status' => Constants::STATUS_DEACTIVATE,
                    'info' => ""
                ];
            $value->supplier = $supplier;

        }
        return $this->respond($sales);
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
        if (!$request->input('sales_id'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your sales_id'
            );
        if (!$request->input('capacity_percent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن درصد ظرفیت اجباری می باشد.'
            );
        $percent = 0;
        $price = 0;
        switch ($request->input('type_price')) {
            case Constants::TYPE_PERCENT:
                $typePrice = Constants::TYPE_PERCENT;
                $percent = $request->input('price_percent');
                break;
            case Constants::TYPE_PRICE:
                $typePrice = Constants::TYPE_PRICE;
                $price = $request->input('price_percent');
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، وارد کردن نوع مبلغ (تومان یا درصد) اجباری می باشد.'
                );
        }
        if (!$request->input('price_percent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن مبلغ اجباری می باشد.'
            );
        switch ($request->input('status')) {
            case Constants::STATUS_ACTIVE:
                $status = Constants::STATUS_ACTIVE;
                break;
            case Constants::STATUS_DEACTIVATE:
                $status = Constants::STATUS_DEACTIVATE;
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، وارد کردن وضعیت (فعال یا غیرفعال) اجباری می باشد.'
                );
        }
        if (SupplierSales::where(['supplier_id' => $request->input('supplier_id'), 'sales_id' => $request->input('sales_id')])->exists())
            SupplierSales::where(['supplier_id' => $request->input('supplier_id'), 'sales_id' => $request->input('sales_id')])->update([
                'capacity_percent' => $this->help->priceNumberDigitsToNormal($request->input('capacity_percent')),
                'type_price' => $typePrice,
                'price' => $this->help->priceNumberDigitsToNormal($price),
                'percent' => $this->help->priceNumberDigitsToNormal($percent),
                'status' => $status,
            ]);
        else
            SupplierSales::create([
                'supplier_id' => $request->input('supplier_id'),
                'sales_id' => $request->input('sales_id'),
                'capacity_percent' => $this->help->priceNumberDigitsToNormal($request->input('capacity_percent')),
                'type_price' => $typePrice,
                'price' => $this->help->priceNumberDigitsToNormal($price),
                'percent' => $this->help->priceNumberDigitsToNormal($percent),
                'status' => $status,
            ]);
        return $this->respond(["status" => "success"]);
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
