<?php

namespace App\Http\Controllers\Api\V1\CP\Crm;

use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Supplier;
use Illuminate\Http\Request;

class SupplierController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $supplier = Supplier::select(
            Constants::SUPPLIER_DB . '.id',
            Constants::SUPPLIER_DB . '.name as supplier_name',
            Constants::SUPPLIER_DB . '.tell',
            Constants::USERS_DB . '.name as user_name',
            Constants::USERS_DB . '.phone',
            Constants::USERS_DB . '.username',
            Constants::USERS_DB . '.password_username'
        )->join(Constants::SUPPLIER_USERS_DB, Constants::SUPPLIER_DB . '.id', '=', Constants::SUPPLIER_USERS_DB . '.supplier_id')
            ->join(Constants::USERS_DB, Constants::SUPPLIER_USERS_DB . '.user_id', '=', Constants::USERS_DB . '.id');
        if ($request->input('search')) {
            $supplier = $supplier->orWhere([
                [Constants::USERS_DB . '.name', 'like', '%' . $request->input("search") . '%']
            ]);
            $supplier = $supplier->orWhere([
                [Constants::SUPPLIER_DB . '.name', 'like', '%' . $request->input("search") . '%']
            ]);
            $supplier = $supplier->orWhere([
                [Constants::USERS_DB . '.phone', 'like', '%' . $request->input("search") . '%']
            ]);
        }
        $supplier = $supplier->orderByDesc(Constants::SUPPLIER_DB . '.created_at')->take(10)->get();
        return $this->respond($supplier);

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


    /////////////////////public function////////////////////
}
