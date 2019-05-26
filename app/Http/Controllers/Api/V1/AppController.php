<?php

namespace App\Http\Controllers\Api\V1;

use App\ApiApp;
use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Sales;
use App\SupplierSales;
use Illuminate\Http\Request;
use App\Http\Requests;

class AppController extends ApiController
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
        //
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

    public function apiChecker(Request $request)
    {
        if (!$request->header('apiId'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your apiId header'
            );
        $appId = ApiApp::where([
            'api_id' => $request->header('apiId')
        ])->pluck('app_id');
        if (!sizeof($appId))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your apiId header'
            );
        return $this->respond(["app_id" => $appId]);
    }

    public function appGetSupplierActiveSales(Request $request)
    {
        switch ($request->header('sales')) {
            case Constants::SALES_TYPE_API :
                $type = Constants::SALES_TYPE_API;
                break;
            case Constants::SALES_TYPE_JUSTKISH :
                $type = Constants::SALES_TYPE_JUSTKISH;
                break;
            case Constants::SALES_TYPE_AGENCY :
                $type = Constants::SALES_TYPE_AGENCY;
                break;
            case Constants::SALES_TYPE_PERCENT_SITE :
                $type = Constants::SALES_TYPE_PERCENT_SITE;
                break;
            case Constants::SALES_TYPE_SEPEHR :
                $type = Constants::SALES_TYPE_SEPEHR;
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_UNAUTHORIZED_401,
                    'Plz check your sales header'
                );
        }
        $sales = Sales::where(
            'type', $type
        )->first();
        $supplierSales = SupplierSales::
        where('capacity_percent', '!=', 0)
            ->where(['status' => Constants::STATUS_ACTIVE, 'sales_id' => $sales->id])
            ->pluck('supplier_id');
        return $this->respond(["supplier_id" => $supplierSales]);
    }
}
