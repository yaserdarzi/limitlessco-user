<?php

namespace App\Http\Controllers\Api\V1\CP\Api;

use App\Api;
use App\App;
use App\Commission;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\ApiApp;
use App\Inside\Constants;
use App\Sales;
use App\SupplierApi;
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

    public function appChecker(Request $request)
    {
        if (!$request->header('appName'))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'Plz check your appName header'
            );
        $app = App::whereIn(
            'id', $request->input('apps_id')
        )->where('app', $request->header('appName'))->first();
        if (!$app)
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'کاربر گرامی شما دسترسی به این قسمت را ندارید.'
            );
        $apiApp = ApiApp::where([
            'api_id' => $request->input('api_id'),
            'app_id' => $app->id,
        ])->get();
        if (!sizeof($apiApp))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'کاربر گرامی شما دسترسی به این قسمت را ندارید.'
            );
        return $this->respond(["app_id" => $app->id]);
    }


    public function getSupplier(Request $request)
    {
        $sales = Sales::where(
            'type', Constants::SALES_TYPE_API
        )->first();
        $supplierSales = SupplierSales::
        where('capacity_percent', '!=', 0)
            ->where(['status' => Constants::STATUS_ACTIVE, 'sales_id' => $sales->id])
            ->pluck('supplier_id');
        $commissions = $this->getCommissions($request);
        return $this->respond($supplierSales, ["commissions" => $commissions,]);
    }

    ///////////////////private function///////////////////////


    private function getCommissions(Request $request)
    {
        $customer_id = Constants::SALES_TYPE_API . "-" . $request->input('api_id');
        $shopping_id = $request->header('appName') . '-';
        $commissions = Commission::
        where([
            'customer_id' => $customer_id,
            ['shopping_id', 'like', "%{$shopping_id}%"],
        ])->get();
        return $commissions;
    }
}
