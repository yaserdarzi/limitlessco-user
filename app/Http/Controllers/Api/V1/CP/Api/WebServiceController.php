<?php

namespace App\Http\Controllers\Api\V1\CP\Api;

use App\ApiWallet;
use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Api;
use App\ApiApp;
use App\ApiUser;
use App\Inside\Helpers;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Intervention\Image\Facades\Image;

class WebServiceController extends ApiController
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
        if (!$apiUser = ApiUser::where(['user_id' => $user->id])->first())
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                "کاربر گرامی شما عرضه کننده نمی باشید."
            );
        if (!$api = Api::where(['id' => $apiUser->api_id, 'status' => Constants::STATUS_ACTIVE])->first())
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                "کاربر گرامی حساب شما فعال نمی باشید."
            );
        $user->wallet = ApiWallet::where(['id' => $apiUser->api_id])->first();
        if ($user->image) {
            $user->image_thumb = url('/files/user/thumb/' . $user->image);
            $user->image = url('/files/user/' . $user->image);
        } else {
            $user->image_thumb = url('/files/user/defaultAvatar.svg');
            $user->image = url('/files/user/defaultAvatar.svg');
        }
        $appId = ApiApp::where(['api_id' => $apiUser->api_id])->pluck('app_id');
        $user->api = Api::where('id', $apiUser->api_id)->first();
        if ($user->api->image) {
            $user->api->image_thumb = url('/files/api/thumb/' . $user->api->image);
            $user->api->image = url('/files/api/' . $user->api->image);
        } else {
            $user->api->image_thumb = url('/files/api/defaultAvatar.svg');
            $user->api->image = url('/files/api/defaultAvatar.svg');
        }
        $user->role = ApiUser::where(['user_id' => $user->id])->first()->role;
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
        Api::where(['id' => $request->input('api_id')])
            ->update([
                'name' => $request->input('name')
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
                'کاربر گرامی ، وارد کردن نام آژانس اجباری می باشد.'
            );
        $info = User::find($request->input('user_id'));
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
                'image' => $image
            ]);
        return $this->index($request);
    }
}
