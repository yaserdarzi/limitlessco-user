<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\Agency;
use App\AgencyAgency;
use App\App;
use App\Commission;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\AgencyApp;
use App\Inside\Constants;
use App\Sales;
use App\SupplierAgency;
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
                $request->header('appName') . 'کاربر گرامی شما دسترسی به این قسمت را ندارید.eeeee'
            );
        $agencyApp = AgencyApp::where([
            'agency_id' => $request->input('agency_id'),
            'app_id' => $app->id,
        ])->get();
        if (!sizeof($agencyApp))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'کاربر گرامی شما دسترسی به این قسمت را ندارید.'
            );
        return $this->respond(["app_id" => $app->id]);
    }

    public function getSupplier(Request $request)
    {
        $agency = Agency::where('id', $request->input('agency_id'))->first();
        $supplierSalesID = [];
        $supplierAgencyID = [];
        $agencyAgencyID = [];
        if (in_array(Constants::AGENCY_INTRODUCTION_SALES, $agency->introduction))
            $supplierSalesID = $this->getSupplierSales();
        if (in_array(Constants::AGENCY_INTRODUCTION_SUPPLIER, $agency->introduction))
            $supplierAgencyID = $this->getSupplierAgency($request);
        if (in_array(Constants::AGENCY_INTRODUCTION_AGENCY, $agency->introduction))
            $agencyAgencyID = $this->getAgencyAgency($request);
        $commissions = $this->getCommissions($request);
        return $this->respond(["commissions" => $commissions, "supplier_sales" => $supplierSalesID, "supplier_agency" => $supplierAgencyID, "agency_agency" => $agencyAgencyID]);
    }

    ///////////////////private function///////////////////////

    private function getCommissions(Request $request)
    {
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id');
        $shopping_id = $request->header('appName') . '-';
        $commissions = Commission::
        where([
            'customer_id' => $customer_id,
            ['shopping_id', 'like', "%{$shopping_id}%"],
        ])->get();
        return $commissions;
    }

    private function getSupplierSales()
    {
        $sales = Sales::where(
            'type', Constants::SALES_TYPE_AGENCY
        )->first();
        $supplierSales = SupplierSales::
        where('capacity_percent', '!=', 0)
            ->where(['status' => Constants::STATUS_ACTIVE, 'sales_id' => $sales->id])
            ->pluck('supplier_id');
        return $supplierSales;
    }

    private function getSupplierAgency(Request $request)
    {
        $supplierAgency = SupplierAgency::
        where('capacity_percent', '!=', 0)
            ->where(['status' => Constants::STATUS_ACTIVE, 'agency_id' => $request->input('agency_id')])
            ->pluck('supplier_id');
        return $supplierAgency;
    }

    private function getAgencyAgency(Request $request)
    {
        $agency_id = AgencyAgency::
        where('capacity_percent', '!=', 0)
            ->where(['status' => Constants::STATUS_ACTIVE, 'agency_id' => $request->input('agency_id')])
            ->pluck('agency_parent_id');
        $agencyId = $agency_id->toArray();
        if (sizeof($agency_id))
            while ($agency_id) {
                $agency_id = AgencyAgency::
                where('capacity_percent', '!=', 0)
                    ->whereIn('agency_id', $agency_id)
                    ->where(['status' => Constants::STATUS_ACTIVE])
                    ->pluck('agency_parent_id');
                if (sizeof($agency_id))
                    $agencyId = array_merge($agencyId, $agency_id->toArray());
                else
                    break;
            }
        $agencyId = array_unique($agencyId);
        $agency = Agency::whereIn('id', $agencyId)
            ->whereJsonContains('introduction', [Constants::AGENCY_INTRODUCTION_SALES])->pluck('id');
        $supplierSalesID = [];
        if (sizeof($agency))
            $supplierSalesID = $this->getSupplierSales();
        $supplierAgencyID = SupplierAgency::where('capacity_percent', '!=', 0)
            ->whereIn('agency_id', $agencyId)
            ->where(['status' => Constants::STATUS_ACTIVE])
            ->pluck('supplier_id');
        $supplierAgency = array_unique(array_merge($supplierSalesID->toArray(), $supplierAgencyID->toArray()));
        return $supplierAgency;
    }
}
