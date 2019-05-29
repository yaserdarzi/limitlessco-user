<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\Agency;
use App\AgencyApp;
use App\AgencyUser;
use App\AgencyWallet;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\SupplierAgency;
use App\SupplierAgencyCategory;
use App\SupplierApp;
use App\User;
use App\Wallet;
use Hashids\Hashids;
use Illuminate\Http\Request;
use App\Http\Requests;

class SupplierAgencyController extends ApiController
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
        $supplierAgency = SupplierAgency::with(['agency', 'category', 'user']);
//        if ($request->input('search'))
//            $supplierAgency = $supplierAgency->where([
//                'status' => Constants::STATUS_ACTIVE,
//                'supplier_id' => $request->input('supplier_id')
//            ]);
        $supplierAgency = $supplierAgency->where([
            'status' => Constants::STATUS_ACTIVE,
            'supplier_id' => $request->input('supplier_id')
        ])->get()->map(function ($value) {
            if ($value->agency->image) {
                $value->agency->image_thumb = url('/files/agency/thumb/' . $value->agency->image);
                $value->agency->image = url('/files/agency/' . $value->agency->image);
            } else {
                $value->agency->image_thumb = url('/files/agency/defaultAvatar.svg');
                $value->agency->image = url('/files/agency/defaultAvatar.svg');
            }
            return $value;
        });
        return $this->respond($supplierAgency);
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
        if (!$request->input('supplier_agency_category_id'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن گروه آژانس اجباری می باشد.'
            );
        if (!$supplierAgencyCategory = SupplierAgencyCategory::where('id', $request->input('supplier_agency_category_id'))->first())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن گروه آژانس اجباری می باشد.'
            );
        if (!$request->input('phone'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره همراه مدیر آژانس اجباری می باشد.'
            );
        if (!$request->input('capacity_percent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ظرفیت اجباری می باشد.'
            );
        $phone = $this->help->phoneChecker($request->input('phone'), '');
        $agency = Agency::join(Constants::AGENCY_USERS_DB, Constants::AGENCY_USERS_DB . '.agency_id', '=', Constants::AGENCY_DB . '.id')
            ->join(Constants::USERS_DB, Constants::AGENCY_USERS_DB . '.user_id', '=', Constants::USERS_DB . '.id')
            ->where('phone', $phone)
            ->first();
        if ($agency) {
            if (SupplierAgency::where(['supplier_id' => $request->input('supplier_id'), 'agency_id' => $agency->agency_id])->exists())
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، آژانس مورد نظر قبلا در سیستم شما ثبت شده است.'
                );
            $agency_id = $agency->agency_id;
        } else {
            $user = User::where(['phone' => $phone])->first();
            if (!$user) {
                $hashIds = new Hashids(config("config.hashIds"));
                $refLink = $hashIds->encode($phone, intval(microtime(true)));
                $user = User::create([
                    'phone' => $phone,
                    'email' => '',
                    'password' => '',
                    'gmail' => '',
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
            $app = SupplierApp::where([
                'supplier_id' => $request->input('supplier_id'),
            ])->get();
            $agency = Agency::create([
                'name' => '',
                'image' => '',
                'tell' => '',
                'type' => 'percent',
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
            foreach ($app as $value) {
                AgencyApp::create([
                    'agency_id' => $agency->id,
                    'app_id' => $value->app_id,
                ]);
            }
            $agency_id = $agency->id;
        }
        SupplierAgency::create([
            'supplier_id' => $request->input('supplier_id'),
            'supplier_agency_category_id' => $request->input('supplier_agency_category_id'),
            'agency_id' => $agency_id,
            'capacity_percent' => $request->input('capacity_percent'),
            'type_price' => $supplierAgencyCategory->type_price,
            'price' => $supplierAgencyCategory->price,
            'percent' => $supplierAgencyCategory->percent,
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
        $supplierAgency = SupplierAgency::
        with(['agency', 'category'])
            ->where([
                'status' => Constants::STATUS_ACTIVE,
                'supplier_id' => $request->input('supplier_id'),
                'id' => $id
            ])->first();
        if ($supplierAgency)
            if ($supplierAgency->agency->image) {
                $supplierAgency->agency->image_thumb = url('/files/agency/thumb/' . $supplierAgency->agency->image);
                $supplierAgency->agency->image = url('/files/agency/' . $supplierAgency->agency->image);
            } else {
                $supplierAgency->agency->image_thumb = url('/files/agency/defaultAvatar.svg');
                $supplierAgency->agency->image = url('/files/agency/defaultAvatar.svg');
            }
        return $this->respond($supplierAgency);
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
        if (!SupplierAgency::where(['supplier_id' => $request->input('supplier_id'), 'id' => $id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، آژانس مورد نظر اشتباه می باشد.'
            );
        $data = array(
            'capacity_percent' => $request->input('capacity_percent'),
            'type_price' => $typePercent,
        );
        $data = array_merge($arrayPercent, $data);
        SupplierAgency::where([
            'supplier_id' => $request->input('supplier_id'),
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
        if (!SupplierAgency::where(['id' => $id, 'status' => Constants::STATUS_ACTIVE])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی شما دسترسی لازم برای حرف را ندارید."
            );
        SupplierAgency::where([
            'id' => $id,
            'supplier_id' => $request->input('supplier_id')
        ])->update(['status' => Constants::STATUS_DEACTIVATE]);
        return $this->respond(["status" => "success"]);
    }
    ///////////////////public function///////////////////////

}
