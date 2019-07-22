<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\Agency;
use App\AgencyAgency;
use App\AgencyAgencyCategory;
use App\AgencyAgencyCategoryCommission;
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
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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
        $hashIds = new Hashids(config("config.hashIds"));
        $refLink = $hashIds->encode($phone, intval(microtime(true)));
        $user = User::create([
            'phone' => $phone,
            'name' => $request->input('name'),
            'username' => strtolower(str_replace(' ', '', $request->input('username'))),
            'password_username' => $this->help->normalizePhoneNumber($request->input('password')),
            "ref_link" => $refLink,
        ]);
        Wallet::create([
            'user_id' => $user->id,
            'price' => 0,
        ]);
        //agency
        $agency = Agency::create([
            'name' => $request->input('name_agency'),
            'image' => '',
            'tell' => $request->input('tell'),
            'city' => $request->input('city'),
            'type' => 'percent',
            'percent' => Constants::AGENCY_PERCENT_DEFAULT,
            'status' => Constants::STATUS_ACTIVE,
            'introduction' => [Constants::AGENCY_INTRODUCTION_AGENCY, Constants::AGENCY_INTRODUCTION_SALES]
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
        $app = AgencyApp::where([
            'agency_id' => $request->input('agency_id'),
        ])->get();
        foreach ($app as $value) {
            AgencyApp::create([
                'agency_id' => $agency->id,
                'app_id' => $value->app_id,
            ]);
        }
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $agency->id;
        ////////////////////Commission////////////////////
        $commissions = AgencyAgencyCategoryCommission::where("agency_agency_category_id", $request->input('agency_agency_category_id'))->get();
        foreach ($commissions as $value) {
            if (!Commission::where(['customer_id' => $customer_id, 'shopping_id' => $value->shopping_id])->exists()) {
                Commission::create([
                    'customer_id' => $customer_id,
                    'shopping_id' => $value->shopping_id,
                    'type' => $value->type,
                    'percent' => $value->percent,
                    'price' => $value->price,
                    'award' => $value->award,
                    'income' => $value->income,
                    'is_price_power_up' => $value->is_price_power_up,
                    'info' => $value->info
                ]);
            }
        }
        AgencyAgency::create([
            'agency_parent_id' => $request->input('agency_id'),
            'agency_agency_category_id' => $request->input('agency_agency_category_id'),
            'agency_id' => $agency->id,
            'capacity_percent' => 0,
            'type_price' => $agencyAgencyCategory->type_price,
            'price' => $agencyAgencyCategory->price,
            'percent' => $agencyAgencyCategory->percent,
        ]);
        if ($phone != '') {
            $connection = new AMQPStreamConnection(config("rabbitmq.server"), config("rabbitmq.port"), config("rabbitmq.user"), config("rabbitmq.password"), '/');
            $channel = $connection->channel();
            $channel->queue_declare(Constants::QUEUE_SMS_REGISTER_AGENCY, false, false, false, false);
            $msg = new AMQPMessage(json_encode([
                'phone' => $phone,
                'agency_name' => $agency->name,
                'user_name' => $user->name,
                'username' => strtolower(str_replace(' ', '', $request->input('username'))),
                'password' => $this->help->normalizePhoneNumber($request->input('password'))
            ]),
                array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
            );
            $channel->basic_publish($msg, '', Constants::QUEUE_SMS_REGISTER_AGENCY);
            $channel->close();
            $connection->close();
        }
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
        ])->delete();
        return $this->respond(["status" => "success"]);
    }
    ///////////////////public function///////////////////////

}
