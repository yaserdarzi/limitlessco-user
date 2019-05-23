<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Supplier;
use App\SupplierApp;
use App\SupplierUser;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Requests;

class SupplierController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::where(['id' => $request->input('user_id')])->first();
        $user->wallet = Wallet::where(['user_id' => $user->id])->first();
        if (!$supplierUser = SupplierUser::where(['user_id' => $user->id])->first())
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                "کاربر گرامی شما عرضه کننده نمی باشید."
            );
        if (!$supplier = Supplier::where(['id' => $supplierUser->supplier_id, 'status' => Constants::STATUS_ACTIVE])->first())
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                "کاربر گرامی حساب شما فعال نمی باشید."
            );
        if ($user->image) {
            $user->image = url('/files/user/' . $user->image);
            $user->image_thumb = url('/files/user/thumb/' . $user->image);
        } else {
            $user->image = url('/files/user/defaultAvatar.svg');
            $user->image_thumb = url('/files/user/defaultAvatar.svg');
        }
        $appId = SupplierApp::where(['supplier_id' => $supplierUser->supplier_id])->pluck('app_id');
        $user->supplier = Supplier::where('id', $supplierUser->supplier_id)->first();
        if ($user->supplier->image) {
            $user->supplier->image = url('/files/supplier/' . $user->supplier->image);
            $user->supplier->image_thumb = url('/files/supplier/thumb/' . $user->supplier->image);
        } else {
            $user->supplier->image = url('/files/supplier/defaultAvatar.svg');
            $user->supplier->image_thumb = url('/files/supplier/defaultAvatar.svg');
        }
        $user->role = SupplierUser::where(['user_id' => $user->id])->first()->role;
        $user->apps = App::whereIn('id', $appId)->get();
        $user->token = $request->header('Authorization');
        $user->appToken = $request->header('appToken');
        return $this->respond($user);
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

}