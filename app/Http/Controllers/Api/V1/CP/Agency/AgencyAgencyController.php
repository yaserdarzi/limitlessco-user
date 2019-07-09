<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\Agency;
use App\AgencyAgency;
use App\AgencyAgencyCategory;
use App\AgencyApp;
use App\AgencyUser;
use App\AgencyWallet;
use App\Commission;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\User;
use App\Wallet;
use Hashids\Hashids;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class AgencyAgencyController extends ApiController
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
        $agencyAgency = AgencyAgency::with(['category', 'user', 'agency'])
            ->where([
                'status' => Constants::STATUS_ACTIVE,
                'agency_parent_id' => $request->input('agency_id')
            ]);
        if ($request->input('search')) {
            $search = $request->input('search');
            $agencyAgency = $agencyAgency->with([
                'agency' => function ($query) use ($search) {
                    $query->Where('name', 'like', '%' . $search . '%');
                },
            ]);
        }
        $agencyAgency = $agencyAgency->get();
        foreach ($agencyAgency as $key => $value) {
            if ($value->agency) {
                if ($value->agency->image) {
                    $value->agency->image_thumb = url('/files/agency/thumb/' . $value->agency->image);
                    $value->agency->image = url('/files/agency/' . $value->agency->image);
                } else {
                    $value->agency->image_thumb = url('/files/agency/defaultAvatar.svg');
                    $value->agency->image = url('/files/agency/defaultAvatar.svg');
                }
            } else
                unset($agencyAgency[$key]);
        }
        return $this->respond($agencyAgency);
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
        if (!$request->input('agency_agency_category_id'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن گروه آژانس اجباری می باشد.'
            );
        if (!$agencyAgencyCategory = AgencyAgencyCategory::where(['id' => $request->input('agency_agency_category_id'), 'agency_id' => $request->input('agency_id')])->first())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن گروه آژانس اجباری می باشد.'
            );
        if (!$request->input('username'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام کاربری آژانس اجباری می باشد.'
            );
        if (!$request->input('capacity_percent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ظرفیت اجباری می باشد.'
            );
        if (!$request->input('password'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن کلمه عبور اجباری می باشد.'
            );
        $username = strtolower(str_replace(' ', '', $request->input('username')));
        $agency = Agency::join(Constants::AGENCY_USERS_DB, Constants::AGENCY_USERS_DB . '.agency_id', '=', Constants::AGENCY_DB . '.id')
            ->join(Constants::USERS_DB, Constants::AGENCY_USERS_DB . '.user_id', '=', Constants::USERS_DB . '.id')
            ->where('username', $username)
            ->first();
        if ($agency) {
            if (AgencyAgency::where(['agency_parent_id' => $request->input('agency_id'), 'agency_id' => $agency->agency_id])->exists())
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، آژانس مورد نظر قبلا در سیستم شما ثبت شده است.'
                );
            $agencyUpdate = Agency::find($agency->agency_id);
            $agencyUpdate->introduction = array_merge($agencyUpdate->introduction, [Constants::AGENCY_INTRODUCTION_AGENCY]);
            $agencyUpdate->save();
            $agency_id = $agency->agency_id;
        } else {
            if (User::where(['username' => strtolower(str_replace(' ', '', $request->input('username')))])->exists())
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، نام کاربری تکراری می باشد.'
                );
            $user = User::where(['username' => $username])->first();
            if (!$user) {
                $hashIds = new Hashids(config("config.hashIds"));
                $refLink = $hashIds->encode($username, intval(microtime(true)));
                $user = User::create([
                    'phone' => '',
                    'email' => '',
                    'password' => '',
                    'gmail' => '',
                    'username' => $username,
                    'password_username' => $request->input('password'),
                    'name' => '',
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
            $app = AgencyApp::where([
                'agency_id' => $request->input('agency_id'),
            ])->get();
            $agency = Agency::create([
                'name' => '',
                'image' => '',
                'tell' => '',
                'type' => 'percent',
                'status' => Constants::STATUS_ACTIVE,
                'introduction' => [Constants::AGENCY_INTRODUCTION_AGENCY]
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
            foreach ($app as $value)
                if (!AgencyApp::where(['agency_id' => $agency->id, 'app_id' => $value->app_id])->exists())
                    AgencyApp::create([
                        'agency_id' => $agency->id,
                        'app_id' => $value->app_id,
                    ]);
            $agency_id = $agency->id;
        }
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $agency_id;
        ////////////////////ROOM////////////////////
        $room = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_ROOM_DB)
            ->select(
                Constants::APP_HOTEL_DB_ROOM_DB . '.id as room_id',
                '*'
            )
            ->get();
        foreach ($room as $roomVal) {
            $shopping_id = Constants::APP_NAME_HOTEL . "-" . $roomVal->hotel_id . "-" . $roomVal->room_id;
            if (!Commission::where(['customer_id' => $customer_id, 'shopping_id' => $shopping_id])->exists()) {
                Commission::create([
                    'customer_id' => $customer_id,
                    'shopping_id' => $shopping_id,
                    'type' => $agencyAgencyCategory->type_price,
                    'price' => $agencyAgencyCategory->price,
                    'percent' => $agencyAgencyCategory->percent,
                ]);
            }

        }
        ////////////PRODUCT////////////////////
        $product = DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_DB)
            ->get();
        foreach ($product as $productVal) {
            $shopping_id = Constants::APP_NAME_ENTERTAINMENT . "-" . $productVal->id;
            if (!Commission::where(['customer_id' => $customer_id, 'shopping_id' => $shopping_id])->exists()) {
                Commission::create([
                    'customer_id' => $customer_id,
                    'shopping_id' => $shopping_id,
                    'type' => $agencyAgencyCategory->type_price,
                    'price' => $agencyAgencyCategory->price,
                    'percent' => $agencyAgencyCategory->percent,
                ]);
            }

        }
        AgencyAgency::create([
            'agency_parent_id' => $request->input('agency_id'),
            'agency_agency_category_id' => $request->input('agency_agency_category_id'),
            'agency_id' => $agency_id,
            'capacity_percent' => $request->input('capacity_percent'),
            'type_price' => $agencyAgencyCategory->type_price,
            'price' => $agencyAgencyCategory->price,
            'percent' => $agencyAgencyCategory->percent,
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
        $agencyAgency = AgencyAgency::
        with(['agency', 'category'])
            ->where([
                'status' => Constants::STATUS_ACTIVE,
                'agency_parent_id' => $request->input('agency_id'),
                'id' => $id
            ])->first();
        if ($agencyAgency)
            if ($agencyAgency->agency->image) {
                $agencyAgency->agency->image_thumb = url('/files/agency/thumb/' . $agencyAgency->agency->image);
                $agencyAgency->agency->image = url('/files/agency/' . $agencyAgency->agency->image);
            } else {
                $agencyAgency->agency->image_thumb = url('/files/agency/defaultAvatar.svg');
                $agencyAgency->agency->image = url('/files/agency/defaultAvatar.svg');
            }
        return $this->respond($agencyAgency);
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('capacity_percent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ظرفیت اجباری می باشد.'
            );
        if (!$request->input('commission'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد  کمیسیون اجباری می باشد.'
            );
        switch ($request->input('type_percent')) {
            case Constants::TYPE_PRICE:
                $typePercent = Constants::TYPE_PRICE;
                $arrayPercent = ['price' => $this->help->priceNumberDigitsToNormal($request->input('commission'))];
                break;
            case Constants::TYPE_PERCENT:
                $typePercent = Constants::TYPE_PERCENT;
                $arrayPercent = ['percent' => $this->help->priceNumberDigitsToNormal($request->input('commission'))];
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، وارد کردن نوع تخفیف (تومان یا درصد) اجباری می باشد.'
                );
        }
        if (!AgencyAgency::where(['agency_parent_id' => $request->input('agency_id'), 'id' => $id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، آژانس مورد نظر اشتباه می باشد.'
            );
        $data = array(
            'capacity_percent' => $request->input('capacity_percent'),
            'type_price' => $typePercent,
        );
        $data = array_merge($arrayPercent, $data);
        AgencyAgency::where([
            'agency_parent_id' => $request->input('agency_id'),
            'id' => $id
        ])->update($data);
        return $this->respond(["status" => "success"]);
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
        if (!AgencyAgency::where(['id' => $id, 'status' => Constants::STATUS_ACTIVE])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی شما دسترسی لازم برای حذف را ندارید."
            );
        AgencyAgency::where([
            'id' => $id,
            'agency_parent_id' => $request->input('agency_id')
        ])->update(['status' => Constants::STATUS_DEACTIVATE]);
        return $this->respond(["status" => "success"]);
    }
    ///////////////////public function///////////////////////

}
