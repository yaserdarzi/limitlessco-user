<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\AgencyUser;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\User;
use App\Wallet;
use Hashids\Hashids;
use Illuminate\Http\Request;

class AgencyUserController extends ApiController
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $agencyUser = AgencyUser::
        join(Constants::USERS_DB, Constants::USERS_DB . '.id', '=', Constants::AGENCY_USERS_DB . '.user_id')
            ->where(['agency_id' => $request->input('agency_id')])
            ->where('user_id', '!=', $request->input('user_id'))
            ->get()->map(function ($value) {
                if ($value->image) {
                    $value->image_thumb = url('/files/user/thumb/' . $value->image);
                    $value->image = url('/files/user/' . $value->image);
                } else {
                    $value->image_thumb = url('/files/user/defaultAvatar.svg');
                    $value->image = url('/files/user/defaultAvatar.svg');
                }

                return $value;
            });
        return $this->respond($agencyUser);
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام و نام خانوادگی اجباری می باشد.'
            );
        if (!$request->input('phone'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره همراه اجباری می باشد.'
            );
        if (!$request->input('percent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن درصد کمیسیون اجباری می باشد.'
            );
        $phone = $this->help->phoneChecker($request->input('phone'), 'IR');
        $user = User::where(['phone' => $phone])->first();
        if (!$user) {
            $hashIds = new Hashids(config("config.hashIds"));
            $refLink = $hashIds->encode($phone, intval(microtime(true)));
            $user = User::create([
                'phone' => $phone,
                'email' => '',
                'password' => '',
                'gmail' => '',
                'name' => $request->input('name'),
                'image' => '',
                'gender' => '',
                "ref_link" => $refLink,
                'info' => '',
                'remember_token' => '',
            ]);
            Wallet::create([
                'user_id' => $user->id,
                'price' => 0,
            ]);
        }
        if (AgencyUser::where(['user_id' => $user->id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی شماره مورد نظر تکراری می باشد."
            );
        AgencyUser::create([
            'user_id' => $user->id,
            'agency_id' => $request->input('agency_id'),
            'type' => 'percent',
            'percent' => $request->input('percent'),
            'role' => Constants::ROLE_COUNTER_MAN
        ]);
        return $this->respond(["status" => "success"]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $agencyUser = AgencyUser::
        join(Constants::USERS_DB, Constants::USERS_DB . '.id', '=', Constants::AGENCY_USERS_DB . '.user_id')
            ->where(['agency_id' => $request->input('agency_id')])
            ->where('user_id', $id)
            ->first();
        if ($agencyUser)
            if ($agencyUser->image) {
                $agencyUser->image_thumb = url('/files/user/thumb/' . $agencyUser->image);
                $agencyUser->image = url('/files/user/' . $agencyUser->image);
            } else {
                $agencyUser->image_thumb = url('/files/user/defaultAvatar.svg');
                $agencyUser->image = url('/files/user/defaultAvatar.svg');
            }
        return $this->respond($agencyUser);
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
//        if ($request->input('role') != Constants::ROLE_ADMIN)
//            throw new ApiException(
//                ApiException::EXCEPTION_NOT_FOUND_404,
//                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
//            );
//        if (!$request->input('name'))
//            throw new ApiException(
//                ApiException::EXCEPTION_NOT_FOUND_404,
//                'کاربر گرامی ، وارد کردن نام و نام خانوادگی اجباری می باشد.'
//            );
//        if (!$request->input('phone'))
//            throw new ApiException(
//                ApiException::EXCEPTION_NOT_FOUND_404,
//                'کاربر گرامی ، وارد کردن شماره همراه اجباری می باشد.'
//            );
//        if (!$request->input('percent'))
//            throw new ApiException(
//                ApiException::EXCEPTION_NOT_FOUND_404,
//                'کاربر گرامی ، وارد کردن درصد کمیسیون اجباری می باشد.'
//            );
//        $phone = $this->help->phoneChecker($request->input('phone'), 'IR');
//        $agencyUser = AgencyUser::
//        join(Constants::USERS_DB, Constants::USERS_DB . '.id', '=', Constants::AGENCY_USERS_DB . '.user_id')
//            ->where([
//                'agency_id' => $request->input('agency_id'),
//                'user_id'=> $id,
//                'phone'=>$phone
//            ])->first();
//
//        dd($agencyUser );
//
//
//        if (AgencyUser::where(['user_id' => $user->id])->exists())
//            throw new ApiException(
//                ApiException::EXCEPTION_NOT_FOUND_404,
//                "کاربر گرامی شماره مورد نظر تکراری می باشد."
//            );
//        AgencyUser::create([
//            'user_id' => $user->id,
//            'agency_id' => $request->input('agency_id'),
//            'type' => 'percent',
//            'percent' => $request->input('percent'),
//            'role' => Constants::ROLE_COUNTER_MAN
//        ]);
//        return $this->respond(["status" => "success"]);
//
//
//        $agencyAgent = AgencyAgent::where(['id' => $request->input('agency_agent_id')])->first();
//        if ($agencyAgent->type != "admin")
//            throw new ApiException(
//                ApiException::EXCEPTION_BAD_REQUEST_400,
//                'your not admin'
//            );
//        if (AgencyAgent::where("email", $request->email)->where('id', '!=', $request->input('agency_agent_id'))->exists())
//            throw new ApiException(
//                ApiException::EXCEPTION_BAD_REQUEST_400,
//                'your email is exists'
//            );
//        if (!AgencyAgent::where("id", $request->input('agent_id'))->exists())
//            throw new ApiException(
//                ApiException::EXCEPTION_BAD_REQUEST_400,
//                'plz check your agent_id'
//            );
//        AgencyAgent::where(['id' => $request->input('agent_id')])
//            ->update([
//                'name' => $request->name,
//                'email' => $request->email,
//                'phone' => $request->phone,
//                'tell' => $request->tell,
//                'percent' => $request->input('percent')
//            ]);
//        return $this->respond(["status" => "success"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!AgencyUser::where(['id' => $id, ['role', '!=', Constants::ROLE_ADMIN]])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی شما دسترسی لازم برای حرف را ندارید."
            );
        AgencyUser::where(['id' => $id, 'agency_id' => $request->input('agency_id')])->delete();
        return $this->respond(["status" => "success"]);
    }
}
