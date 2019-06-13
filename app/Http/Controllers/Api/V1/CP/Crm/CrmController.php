<?php

namespace App\Http\Controllers\Api\V1\CP\Crm;

use App\Crm;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;

class CrmController extends ApiController
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
        $user = User::where(['id' => $request->input('user_id')])->first();
        if (!$crm = Crm::where(['user_id' => $user->id])->first())
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                "کاربر گرامی شما مدیر نمی باشید."
            );
        if ($crm->status != Constants::STATUS_ACTIVE)
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                "کاربر گرامی حساب شما فعال نمی باشید."
            );
        if ($user->image) {
            $user->image_thumb = url('/files/user/thumb/' . $user->image);
            $user->image = url('/files/user/' . $user->image);
        } else {
            $user->image_thumb = url('/files/user/defaultAvatar.svg');
            $user->image = url('/files/user/defaultAvatar.svg');
        }
        $user->role = $crm->role;
        $user->token = $request->header('Authorization');
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
    public function update(Request $request)
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


    public function userUpdate(Request $request)
    {
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام اجباری می باشد.'
            );
        $info = User::find($request->input('user_id'));
        $email = $info->email;
        if ($request->input('email')) {
            if (User::where(['email' => $request->input('email'), ['id', '!=', $request->input('user_id')]])->exists())
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، پست الکترونیک تکراری می باشد.'
                );
            $email = $request->input('email');
        }
        $phone = $info->phone;
        if ($request->input('phone')) {
            $phone = $this->help->phoneChecker($request->input('phone'));
            if (User::where(['phone' => $phone, ['id', '!=', $request->input('user_id')]])->exists())
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، تلفن همراه تکراری می باشد.'
                );
        }
        $image = $info->image;
        if ($request->file('image')) {
            \Storage::disk('upload')->makeDirectory('/user/');
            \Storage::disk('upload')->makeDirectory('/user/thumb/');
            $image = md5(\File::get($request->file('image'))) . '.' . $request->file('image')->getClientOriginalExtension();
            $exists = \Storage::disk('upload')->has('/user/' . $image);
            if ($exists == null)
                \Storage::disk('upload')->put('/user/' . $image, \File::get($request->file('image')->getRealPath()));
            //generate thumbnail
            $image_resize = Image::make($request->file('image')->getRealPath());
            //get width and height of image
            $data = getimagesize($request->file('image'));
            $imageWidth = $data[0];
            $imageHeight = $data[1];
            $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 200, 200, false);
            $image_resize->resize($newDimen[0], $newDimen[1]);
            $thumb = public_path('/files/user/thumb/' . $image);
            $image_resize->save($thumb);
        }
        $user = User::find($request->input('user_id'));
        $user->name = $request->input('name');
        $user->image = $image;
        $user->email = $email;
        $user->phone = $phone;
        $user->info = array_merge((array)$user->info, [
            "limitless" => [
                "whats_app" => $request->input('whats_app'),
                "telegram" => $request->input('telegram')
            ]]);
        $user->save();
        return $this->index($request);
    }

    public function userChangePassword(Request $request)
    {
        if (!$request->input('old_password'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن کلمه عبور قدیم اجباری می باشد.'
            );
        if (!$request->input('password'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن کلمه عبور اجباری می باشد.'
            );
        if (!$request->input('re_password'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن کلمه عبور مجدد اجباری می باشد.'
            );
        $info = User::find($request->input('user_id'));
        if ($info->password_username != $request->input('old_password'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، کلمه عبور قدیم شما اشتباه می باشد.'
            );
        if ($request->input('password') != $request->input('re_password'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، کلمه عبور برابر نمی باشد.'
            );
        User::where(['id' => $request->input('user_id')])
            ->update(['password_username' => $request->input('password')]);
        return $this->index($request);
    }

}
