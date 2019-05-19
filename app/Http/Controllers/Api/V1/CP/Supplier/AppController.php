<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\ApiApp;
use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\SupplierApp;
use Firebase\JWT\JWT;
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
        if (!$request->header('appToken'))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'Plz check your appToken header'
            );
        if (!$request->header('appName'))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'Plz check your appName header'
            );
        $app = App::whereIn(
            'id', $request->input('apps_id')
        )->where('app', $request->header('appName'))->first();
        $supplierApp = SupplierApp::where([
            'supplier_id' => $request->input('supplier_id'),
            'app_id' => $app->id,
        ])->get();
        if (!$supplierApp)
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'کاربر گرامی شما دسترسی به این قسمت را ندارید.'
            );
        return $this->respond(["app_id" => $app->id]);
    }
}
