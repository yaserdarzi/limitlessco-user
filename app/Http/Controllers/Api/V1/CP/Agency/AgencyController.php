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
use App\Inside\Helpers;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Intervention\Image\Facades\Image;

class AgencyController extends ApiController
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
            $user->image_thumb = url('/files/user/thumb/' . $user->image);
            $user->image = url('/files/user/' . $user->image);
        } else {
            $user->image_thumb = url('/files/user/defaultAvatar.svg');
            $user->image = url('/files/user/defaultAvatar.svg');
        }
        $appId = AgencyApp::where(['agency_id' => $agencyUser->agency_id])->pluck('app_id');
        $user->agency = Agency::where('id', $agencyUser->agency_id)->first();
        if ($user->agency->image) {
            $user->agency->image_thumb = url('/files/agency/thumb/' . $user->agency->image);
            $user->agency->image = url('/files/agency/' . $user->agency->image);
        } else {
            $user->agency->image_thumb = url('/files/agency/defaultAvatar.svg');
            $user->agency->image = url('/files/agency/defaultAvatar.svg');
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
    public function update(Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام آژانس اجباری می باشد.'
            );
        if (!$request->input('tell'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره آژانس اجباری می باشد.'
            );
        $info = Agency::find($request->input('agency_id'));
        $image = $info->image;
        if ($request->file('image')) {
            \Storage::disk('upload')->makeDirectory('/agency/');
            \Storage::disk('upload')->makeDirectory('/agency/thumb/');
            $image = md5(\File::get($request->file('image'))) . '.' . $request->file('image')->getClientOriginalExtension();
            $exists = \Storage::disk('upload')->has('/agency/' . $image);
            if ($exists == null)
                \Storage::disk('upload')->put('/agency/' . $image, \File::get($request->file('image')->getRealPath()));
            //generate thumbnail
            $image_resize = Image::make($request->file('image')->getRealPath());
            //get width and height of image
            $data = getimagesize($request->file('image'));
            $imageWidth = $data[0];
            $imageHeight = $data[1];
            $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 200, 200, false);
            $image_resize->resize($newDimen[0], $newDimen[1]);
            $thumb = public_path('/files/agency/thumb/' . $image);
            $image_resize->save($thumb);
        }
        Agency::where(['id' => $request->input('agency_id')])
            ->update([
                'name' => $request->input('name'),
                'tell' => $request->input('tell'),
                'image' => $image
            ]);
        return $this->index($request);

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
        if (!$request->input('email'))
            $email = $request->input('email');
        $tell = $info->tell;
        if (!$request->input('tell'))
            $tell = $request->input('tell');
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
        User::where(['id' => $request->input('user_id')])
            ->update([
                'name' => $request->input('name'),
                'email' => $email,
                'tell' => $tell,
                'image' => $image
            ]);
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
