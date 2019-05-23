<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\AgencyWallet;
use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Agency;
use App\AgencyApp;
use App\AgencyUser;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;

class AgencyController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::where(['id' => $request->input('user_id')])->first();
        if (!$agencyUser = AgencyUser::where(['user_id' => $user->id])->first())
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                "کاربر گرامی شما عرضه کننده نمی باشید."
            );
        if (!$agency = Agency::where(['id' => $agencyUser->agency_id, 'status' => Constants::STATUS_ACTIVE])->first())
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                "کاربر گرامی حساب شما فعال نمی باشید."
            );
        $user->wallet = AgencyWallet::where(['id' => $agencyUser->agency_id])->first();
        if ($user->image) {
            $user->image = url('/files/user/' . $user->image);
            $user->image_thumb = url('/files/user/thumb/' . $user->image);
        } else {
            $user->image = url('/files/user/defaultAvatar.svg');
            $user->image_thumb = url('/files/user/defaultAvatar.svg');
        }
        $appId = AgencyApp::where(['agency_id' => $agencyUser->agency_id])->pluck('app_id');
        $user->agency = Agency::where('id', $agencyUser->agency_id)->first();
        if ($user->agency->image) {
            $user->agency->image = url('/files/agency/' . $user->agency->image);
            $user->agency->image_thumb = url('/files/agency/thumb/' . $user->agency->image);
        } else {
            $user->agency->image = url('/files/agency/defaultAvatar.svg');
            $user->agency->image_thumb = url('/files/agency/defaultAvatar.svg');
        }
        $user->role = AgencyUser::where(['user_id' => $user->id])->first()->role;
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
