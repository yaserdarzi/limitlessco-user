<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\Inside\Helpers;
use App\Supplier;
use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\SupplierApp;
use App\SupplierUser;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;
use App\Http\Requests;
use Intervention\Image\Facades\Image;

class SupplierController extends ApiController
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
            $user->image_thumb = url('/files/user/thumb/' . $user->image);
            $user->image = url('/files/user/' . $user->image);
        } else {
            $user->image_thumb = url('/files/user/defaultAvatar.svg');
            $user->image = url('/files/user/defaultAvatar.svg');
        }
        $appId = SupplierApp::where(['supplier_id' => $supplierUser->supplier_id])->pluck('app_id');
        $user->supplier = Supplier::where('id', $supplierUser->supplier_id)->first();
        if ($user->supplier->image) {
            $user->supplier->image_thumb = url('/files/supplier/thumb/' . $user->supplier->image);
            $user->supplier->image = url('/files/supplier/' . $user->supplier->image);
        } else {
            $user->supplier->image_thumb = url('/files/supplier/defaultAvatar.svg');
            $user->supplier->image = url('/files/supplier/defaultAvatar.svg');
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
                'کاربر گرامی ، وارد کردن نام عرضه کننده اجباری می باشد.'
            );
        if (!$request->input('tell'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره تماس عرضه کننده اجباری می باشد.'
            );
        $info = Supplier::find($request->input('supplier_id'));
        $image = $info->image;
        if ($request->file('image')) {
            \Storage::disk('upload')->makeDirectory('/supplier/');
            \Storage::disk('upload')->makeDirectory('/supplier/thumb/');
            $image = md5(\File::get($request->file('image'))) . '.' . $request->file('image')->getClientOriginalExtension();
            $exists = \Storage::disk('upload')->has('/supplier/' . $image);
            if ($exists == null)
                \Storage::disk('upload')->put('/supplier/' . $image, \File::get($request->file('image')->getRealPath()));
            //generate thumbnail
            $image_resize = Image::make($request->file('image')->getRealPath());
            //get width and height of image
            $data = getimagesize($request->file('image'));
            $imageWidth = $data[0];
            $imageHeight = $data[1];
            $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 200, 200, false);
            $image_resize->resize($newDimen[0], $newDimen[1]);
            $thumb = public_path('/files/supplier/thumb/' . $image);
            $image_resize->save($thumb);
        }
        Supplier::where(['id' => $request->input('supplier_id')])
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
                'email' => $request->input('email'),
                'tell' => $request->input('tell'),
                'image' => $image
            ]);
        return $this->index($request);
    }
}
