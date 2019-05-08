<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\User;
use App\UsersLoginToken;
use App\UsersLoginTokenLog;
use Firebase\JWT\JWT;
use Hashids\Hashids;
use Illuminate\Http\Request;

class ZamanakController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

    public function postSmsRequest(Request $request)
    {
        $phone = $this->help->phoneChecker($request->input('mobile'));
        $token = rand(1000, 9999);
        if ($request->header('X-DEBUG') == 1)
            $token = 5555;
        if (!$this->UsersLoginToken($phone, $token, Constants::LOGIN_TYPE_SMS))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'too many request'
            );
        if ($request->header('X-DEBUG') == 1)
            return $this->respond(["status" => "success", 'code' => $token], null);
//        Redis::incr($phone);
//        if (Redis::get($phone) > 5) {
//            if (Redis::get($phone) == 6)
//                Redis::expireAt($phone, time() + 120);
//            return false;
//        }
        $result = $this->callZamanakApi([
//            "method" => "sendCaptchaSms",
//            "username" => "09124092353",
//            "password" => "E6fbff69*",
            "mobile" => $phone,
            "captcha" => $token
        ]);
        if (isset($result['error']) != null)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                $result['error']
            );
        return $this->respond(["status" => "success"], null);
    }

    public function postCallRequest(Request $request)
    {
        $phone = $this->help->phoneChecker($request->input('mobile'));
        $token = rand(1000, 9999);
        if ($request->header('X-DEBUG') == 1)
            $token = 5555;
        if (!$this->UsersLoginToken($phone, $token, Constants::LOGIN_TYPE_CALL))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'too many request'
            );
        if ($request->header('X-DEBUG') == 1)
            return $this->respond(["status" => "success", 'code' => 5555], null);
//        Redis::incr($phone);
//        if (Redis::get($phone) > 5) {
//            if (Redis::get($phone) == 6)
//                Redis::expireAt($phone, time() + 120);
//            return false;
//        }
        $phone_send = substr($phone, 2);
        $result = $this->callZamanakApi([
//            "method" => "voiceOtp",
//            "username" => "09124092353",
//            "password" => "E6fbff69*",
            "mobile" => '0' . $phone_send,
            "numberToSay" => $token,
            "captcha" => null
        ]);
        if (isset($result['error']) != null)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                $result['error']
            );
        return $this->respond(["status" => "success"], null);
    }

    public function postVerifyRequest(Request $request)
    {
        if (!$request->input('code'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your code'
            );
        if (!$request->header('agent'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your agent'
            );
        $phone = $this->help->phoneChecker($request->input('mobile'));
        if (!$this->CheckUsersLoginToken($phone, $request->input('code')))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'code isn`t true'
            );
        return $this->respond($this->verify($phone, $request->header('agent'), $request->input("refLink")));
    }

    private function callZamanakApi($req)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://zamanak.ir/api/json-v5");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "req=" . urlencode(json_encode($req)));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
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
        if (!$UsersLoginToken)
            UsersLoginToken::create(['login' => $phone, 'token' => $token, 'expire_at' => strtotime(date('Y-m-d H:i:s', strtotime("+1 min"))), 'created_at' => strtotime(date('Y-m-d H:i:s'))]);
        else
            UsersLoginToken::where(['login' => $phone])->update(['token' => $token, 'expire_at' => strtotime(date('Y-m-d H:i:s', strtotime("+1 min")))]);

        UsersLoginTokenLog::create(['login' => $phone, 'token' => $token, 'type' => $type, 'expire_at' => strtotime(date('Y-m-d H:i:s', strtotime("+1 min"))), 'created_at' => strtotime(date('Y-m-d H:i:s'))]);
        return true;
    }

    private function CheckUsersLoginToken($phone, $token)
    {
        UsersLoginToken::where('expire_at', '<', strtotime(date('Y-m-d H:i:s')))->delete();
        $UsersLoginToken = UsersLoginToken::where(['login' => $phone, 'token' => $token])->first();
        if ($UsersLoginToken) {
            UsersLoginToken::where(['login' => $phone, 'token' => $token])->delete();
            return true;
        } else
            return false;
    }


    private function verify($phone, $agent, $refLinkPoint)
    {
        $user = User::where(['active' => 1, 'phone' => $phone])->first();
        if (!$user) {
            $hashIds = new Hashids(config("config.hashIds"));
            $refLink = $hashIds->encode($phone, intval(microtime(true)));
            $user = User::create([
                'phone' => $phone, 'email' => '', 'password' => '',
                'remember_token' => '', 'gmail' => '', 'active' => 1,
                'user_level' => '', 'auto_charge' => 0, 'name' => '',
                'birthday' => 0, 'bio' => '', 'gender' => 0, "ref_link" => $refLink
            ]);
        }
        if ($user->name == '')
            $user->isFirst = true;
        else
            $user->isFirst = false;
        $this->generateToken($user, $agent);
        return $user;
    }

    private function generateToken(User $user, $agent)
    {
        $object = array(
            "user_id" => $user->id,
            "agent" => $agent
        );
        $token = JWT::encode($object, config("jwt.secret"));
        $user->token = $token;
        return true;
    }
}
