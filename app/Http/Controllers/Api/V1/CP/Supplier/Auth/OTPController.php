<?php

namespace App\Http\Controllers\Api\V1\Cp\Supplier\Auth;

use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Supplier;
use App\SupplierApp;
use App\SupplierUser;
use App\SupplierWallet;
use App\User;
use App\UsersLoginToken;
use App\UsersLoginTokenLog;
use App\Wallet;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class OTPController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

    public function smsOTP(Request $request)
    {
        $phone = $this->help->phoneChecker($request->input('mobile'), $request->input('country'));
        return $this->sendSMS($request, $phone);
    }

    public function verifyOTP(Request $request)
    {
        if (!$request->header('agent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'Plz check your agent'
            );
        if (!$request->input('code'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'لطفا کد را وارد نمایید.'
            );
        $phone = $this->help->phoneChecker($request->input('mobile'), $request->input('country'));
        if (!$this->CheckUsersLoginToken($phone, $this->help->normalizePhoneNumber($request->input('code'))))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کد صحیح نمی باشد."
            );
        return $this->respond($this->verify($phone, $request));
    }

    public function login(Request $request)
    {
        if (!$request->header('agent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'Plz check your agent'
            );
        if (!$request->input('username'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، لطفا username را وارد نمایید.'
            );
        if (!$request->input('password'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، لطفا password را وارد نمایید.'
            );
        $user = User::where(['username' => strtolower($request->input('username')), 'password_username' => $request->input('password')])->first();
        if (!$user)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، لطفا username,password را چک نمایید.'
            );
        $supplierUser = SupplierUser::where(['user_id' => $user->id])->first();
        if (!$supplierUser)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی عرضه کننده نمی باشید."
            );
        $supplier = Supplier::where('id', $supplierUser->supplier_id)->first();
        if (!$supplier)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی عرضه کننده نمی باشید."
            );
        if ($supplier->status != Constants::STATUS_ACTIVE)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی حساب شما فعال نمی باشید."
            );
        $user->wallet = SupplierWallet::where(['id' => $supplierUser->supplier_id])->first();
        $appId = SupplierApp::where(['supplier_id' => $supplierUser->supplier_id])->pluck('app_id');
        $user->supplier = Supplier::where('id', $supplierUser->supplier_id)->first();
        $user->role = SupplierUser::where(['user_id' => $user->id])->first()->role;
        $user->apps = App::whereIn('id', $appId)->get();
        $this->generateToken($user, $request->header('agent'), $user->role);
        $this->generateAppToken($user, $request->header('agent'), $appId, $supplierUser->supplier_id);
        $this->help->storeUsersLoginLog($user->id, Constants::SUPPLIER_DB . "-" . $supplier->id, Constants::LOGIN_TYPE_USER_PASS, $request->getClientIp());
        return $this->respond($user);
    }

    ////////////////////private function///////////////////////

    private function sendSMS($request, $phone)
    {
        $token = rand(1000, 9999);
        if ($request->header('X-DEBUG') == 1)
            $token = 1010;
        if (!$this->UsersLoginToken($phone, $token, Constants::LOGIN_TYPE_SMS))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "درخواست زیاد لطفا کمی صبر کنید."
            );
        if ($request->header('X-DEBUG') == 1)
            return $this->respond(["phone" => $phone, "status" => "success", 'code' => $token], null);
        Redis::incr($phone);
        if (Redis::get($phone) > 5) {
            if (Redis::get($phone) == 6)
                Redis::expireAt($phone, time() + 120);
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "درخواست زیاد لطفا کمی صبر کنید."
            );
        }
        $result = $this->callKavenegarApi([
            "receptor" => "00" . $phone,
            "type" => "sms",
            "template" => config("OtpConfig.OTP_template"),
            "token" => $token
        ], config("OtpConfig.Kavenegar_api_key"));
        if ($result["return"]["status"] != 200)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                $result["return"]["message"]
            );
        return $this->respond(["phone" => $phone, "status" => "success"]);
    }

    private function callKavenegarApi($req, $kavenegar_api_key)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.kavenegar.com/v1/" . $kavenegar_api_key . "/verify/lookup.json");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: multipart/form-data;'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: */*'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $server_output = json_decode($server_output, true);
        curl_close($ch);
        return $server_output;
    }

    private function UsersLoginToken($phone, $token, $type)
    {
        UsersLoginToken::where('expire_at', '<', strtotime(date('Y-m-d H:i:s')))->delete();
        $UsersLoginToken = UsersLoginToken::where(['login' => $phone])->first();
        if (!$UsersLoginToken) {
            UsersLoginToken::create(['login' => $phone, 'token' => $token, 'expire_at' => strtotime(date('Y-m-d H:i:s', strtotime("+1 min"))), 'created_at' => strtotime(date('Y-m-d H:i:s'))]);
            UsersLoginTokenLog::create(['login' => $phone, 'token' => $token, 'type' => $type, 'expire_at' => strtotime(date('Y-m-d H:i:s', strtotime("+1 min"))), 'created_at' => strtotime(date('Y-m-d H:i:s'))]);
            return true;
        } else {
            UsersLoginToken::where(['login' => $phone])->update(['token' => $token, 'expire_at' => strtotime(date('Y-m-d H:i:s', strtotime("+1 min")))]);
            UsersLoginTokenLog::create(['login' => $phone, 'token' => $token, 'type' => $type, 'expire_at' => strtotime(date('Y-m-d H:i:s', strtotime("+1 min"))), 'created_at' => strtotime(date('Y-m-d H:i:s'))]);
            return true;
        }
    }

    private function CheckUsersLoginToken($phone, $token)
    {
        UsersLoginToken::where('expire_at', '<', strtotime(date('Y-m-d H:i:s')))->delete();
        $UsersLoginToken = UsersLoginToken::where(['login' => $phone, 'token' => $token])->first();
        if ($UsersLoginToken) {
            return true;
        } else
            return false;
    }

    private function verify($phone, Request $request)
    {
        $user = User::where(['phone' => $phone])->first();
        if (!$user)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی شما عرضه کننده نمی باشید."
            );
        $user->wallet = Wallet::where(['user_id' => $user->id])->first();
        if (!$supplierUser = SupplierUser::where(['user_id' => $user->id])->first())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی شما عرضه کننده نمی باشید."
            );
        if (!$supplier = Supplier::where(['id' => $supplierUser->supplier_id, 'status' => Constants::STATUS_ACTIVE])->first())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                "کاربر گرامی حساب شما فعال نمی باشید."
            );
        $appId = SupplierApp::where(['supplier_id' => $supplierUser->supplier_id])->pluck('app_id');
        $user->supplier = Supplier::where('id', $supplierUser->supplier_id)->first();
        $user->role = SupplierUser::where(['user_id' => $user->id])->first()->role;
        $user->apps = App::whereIn('id', $appId)->get();
        $this->generateToken($user, $request->header('agent'), $user->role);
        $this->generateAppToken($user, $request->header('agent'), $appId, $supplierUser->supplier_id);
        $this->help->storeUsersLoginLog($user->id, Constants::SUPPLIER_DB . "-" . $supplier->id, Constants::LOGIN_TYPE_SMS, $request->getClientIp());
        UsersLoginToken::where(['login' => $phone, 'token' => $this->help->normalizePhoneNumber($request->input('code'))])->delete();
        return $user;
    }

    private function generateToken($user, $agent, $userRole)
    {
        $object = array(
            "user_id" => $user->id,
            "agent" => $agent,
            "role" => $userRole,
        );
        $token = JWT::encode($object, config("jwt.secret"));
        $user->token = $token;
        return true;
    }

    private function generateAppToken($user, $agent, $appId, $supplierId)
    {
        $object = array(
            "user_id" => $user->id,
            "apps_id" => $appId,
            "supplier_id" => $supplierId,
            "agent" => $agent,
        );
        $token = JWT::encode($object, config("jwt.secret"));
        $user->appToken = $token;
        return true;
    }
}
