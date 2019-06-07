<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\SupplierUser;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\User;
use App\Wallet;
use Hashids\Hashids;
use Illuminate\Http\Request;

class SupplierUserController extends ApiController
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
        $supplierUser = SupplierUser::
        join(Constants::USERS_DB, Constants::USERS_DB . '.id', '=', Constants::SUPPLIER_USERS_DB . '.user_id')
            ->where(['supplier_id' => $request->input('supplier_id')])
            ->where('user_id', '!=', $request->input('user_id'))
            ->select(
                Constants::SUPPLIER_USERS_DB . '.id',
                'user_id',
                'role',
                'name',
                'phone'
            )->get()->map(function ($value) {
                if ($value->image)
                    $value->image_thumb = url('/files/user/thumb/' . $value->image);
                else
                    $value->image_thumb = url('/files/user/defaultAvatar.svg');

                return $value;
            });
        return $this->respond($supplierUser);
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
        if (SupplierUser::where(['user_id' => $user->id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی شماره مورد نظر تکراری می باشد."
            );
        SupplierUser::create([
            'user_id' => $user->id,
            'supplier_id' => $request->input('supplier_id'),
            'type' => Constants::TYPE_PRICE,
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
        $supplierUser = SupplierUser::
        join(Constants::USERS_DB, Constants::USERS_DB . '.id', '=', Constants::SUPPLIER_USERS_DB . '.user_id')
            ->where(['supplier_id' => $request->input('supplier_id')])
            ->where(Constants::SUPPLIER_USERS_DB . '.id', $id)
            ->first();
        if ($supplierUser)
            if ($supplierUser->image) {
                $supplierUser->image_thumb = url('/files/user/thumb/' . $supplierUser->image);
                $supplierUser->image = url('/files/user/' . $supplierUser->image);
            } else {
                $supplierUser->image_thumb = url('/files/user/defaultAvatar.svg');
                $supplierUser->image = url('/files/user/defaultAvatar.svg');
            }
        return $this->respond($supplierUser);
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
//        $SupplierUser = SupplierUser::
//        join(Constants::USERS_DB, Constants::USERS_DB . '.id', '=', Constants::Supplier_USERS_DB . '.user_id')
//            ->where([
//                'Supplier_id' => $request->input('Supplier_id'),
//                'user_id'=> $id,
//                'phone'=>$phone
//            ])->first();
//
//        dd($SupplierUser );
//
//
//        if (SupplierUser::where(['user_id' => $user->id])->exists())
//            throw new ApiException(
//                ApiException::EXCEPTION_NOT_FOUND_404,
//                "کاربر گرامی شماره مورد نظر تکراری می باشد."
//            );
//        SupplierUser::create([
//            'user_id' => $user->id,
//            'Supplier_id' => $request->input('Supplier_id'),
//            'type' => 'percent',
//            'percent' => $request->input('percent'),
//            'role' => Constants::ROLE_COUNTER_MAN
//        ]);
//        return $this->respond(["status" => "success"]);
//
//
//        $SupplierAgent = SupplierAgent::where(['id' => $request->input('Supplier_agent_id')])->first();
//        if ($SupplierAgent->type != "admin")
//            throw new ApiException(
//                ApiException::EXCEPTION_BAD_REQUEST_400,
//                'your not admin'
//            );
//        if (SupplierAgent::where("email", $request->email)->where('id', '!=', $request->input('Supplier_agent_id'))->exists())
//            throw new ApiException(
//                ApiException::EXCEPTION_BAD_REQUEST_400,
//                'your email is exists'
//            );
//        if (!SupplierAgent::where("id", $request->input('agent_id'))->exists())
//            throw new ApiException(
//                ApiException::EXCEPTION_BAD_REQUEST_400,
//                'plz check your agent_id'
//            );
//        SupplierAgent::where(['id' => $request->input('agent_id')])
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
        if (!SupplierUser::where(['id' => $id, ['role', '!=', Constants::ROLE_ADMIN]])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی شما دسترسی لازم برای حذف را ندارید."
            );
        SupplierUser::where(['id' => $id, 'supplier_id' => $request->input('supplier_id')])->delete();
        return $this->respond(["status" => "success"]);
    }
}
