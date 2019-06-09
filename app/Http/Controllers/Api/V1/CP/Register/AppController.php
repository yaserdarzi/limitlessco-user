<?php

namespace App\Http\Controllers\Api\V1\CP\Register;

use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
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

    public function appSupplier(Request $request)
    {
        $app = App::where(['is_supplier' => true])->get();
        return $this->respond($app);
    }

    public function appAgency(Request $request)
    {
        $app = App::where(['is_agency' => true])->get();
        return $this->respond($app);
    }
    public function appApi(Request $request)
    {
        $app = App::where(['is_api' => true])->get();
        return $this->respond($app);
    }
}
