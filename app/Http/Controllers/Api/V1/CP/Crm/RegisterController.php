<?php

namespace App\Http\Controllers\Api\V1\CP\Crm;

use App\Agency;
use App\AgencyApp;
use App\AgencyUser;
use App\AgencyWallet;
use App\Api;
use App\ApiApp;
use App\ApiUser;
use App\ApiWallet;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\User;
use App\Wallet;
use Hashids\Hashids;
use Illuminate\Http\Request;

class RegisterController extends ApiController
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
        if (!$request->input('name_agency'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام آژانس اجباری می باشد.'
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام و نام خانوادگی اجباری می باشد.'
            );
        if (!$request->input('username'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام کاربری اجباری می باشد.'
            );
        if (!$request->input('password'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن کلمه عبور اجباری می باشد.'
            );
        if ($request->input('username'))
            if (User::where(['username' => strtolower(str_replace(' ', '', $request->input('username')))])->exists())
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، نام کاربری تکراری می باشد.'
                );
        $phone = null;
        if ($request->input('phone')) {
            $phone = $this->help->phoneChecker($request->input('phone'));
            if (User::where(['phone' => $phone])->exists())
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، تلفن همراه تکراری می باشد.'
                );
        }
        $hashIds = new Hashids(config("config.hashIds"));
        $api = Api::create([
            'name' => $request->input('name_agency'),
            'type' => 'percent',
            'percent' => Constants::AGENCY_PERCENT_DEFAULT,
            'status' => Constants::STATUS_ACTIVE,
        ]);
        $refLink = $hashIds->encode($phone, intval(microtime(true)));
        $user = User::create([
            'phone' => $phone,
            'name' => $request->input('name'),
            'username' => strtolower(str_replace(' ', '', $request->input('username'))),
            'password_username' => $request->input('password'),
            "ref_link" => $refLink,
        ]);
        Wallet::create([
            'user_id' => $user->id,
            'price' => 0,
        ]);
        ApiUser::create([
            'user_id' => $user->id,
            'api_id' => $api->id,
            'role' => Constants::ROLE_ADMIN
        ]);
        ApiWallet::create([
            'api_id' => $api->id,
            'price' => 0
        ]);
        ApiApp::create([
            'api_id' => $api->id,
            'app_id' => 2,
        ]);
        //agency
        $agency = Agency::create([
            'name' => $request->input('name_agency'),
            'image' => '',
            'tell' => '',
            'type' => 'percent',
            'percent' => Constants::AGENCY_PERCENT_DEFAULT,
            'status' => Constants::STATUS_ACTIVE,
            'introduction' => [Constants::AGENCY_INTRODUCTION_SALES]
        ]);
        AgencyUser::create([
            'user_id' => $user->id,
            'agency_id' => $agency->id,
            'type' => 'percent',
            'percent' => 100,
            'role' => Constants::ROLE_ADMIN
        ]);
        AgencyWallet::create([
            'agency_id' => $agency->id,
            'price' => 0
        ]);
        AgencyApp::create([
            'agency_id' => $agency->id,
            'app_id' => 2,
        ]);
        return $this->respond(["username" => $user->username, "password" => $request->input('password')]);
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
}
