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
use App\Commission;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\User;
use App\Wallet;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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
            'password_username' => $this->help->normalizePhoneNumber($request->input('password')),
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
            'tell' => $request->input('tell'),
            'city' => $request->input('city'),
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
        ////////////////////Hotel App////////////////////
        if (!AgencyApp::where(['agency_id' => $agency->id, 'app_id' => 2])->exists()) {
            AgencyApp::create([
                'agency_id' => $agency->id,
                'app_id' => 2,
            ]);
        }
        ////////////////////Entertainment App////////////////////
        if (!AgencyApp::where(['agency_id' => $agency->id, 'app_id' => 1])->exists()) {
            AgencyApp::create([
                'agency_id' => $agency->id,
                'app_id' => 1,
            ]);
        }
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $agency->id;
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
                    'type' => $agency->type,
                    'percent' => Constants::AGENCY_PERCENT_DEFAULT,
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
                if ($productVal->id == 1)
                    $percent = Constants::AGENCY_PERCENT_DEFAULT;
                else
                    $percent = 30;
                Commission::create([
                    'customer_id' => $customer_id,
                    'shopping_id' => $shopping_id,
                    'type' => $agency->type,
                    'percent' => $percent,
                ]);
            }
        }
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
        return $this->respond(["name" => $user->name, "username" => $user->username, "password" => $this->help->normalizePhoneNumber($request->input('password'))]);
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
