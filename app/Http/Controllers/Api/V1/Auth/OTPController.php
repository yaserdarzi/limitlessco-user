<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\App;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\User;
use App\UserApps;
use App\UsersLoginToken;
use App\UsersLoginTokenLog;
use App\UsersRefer;
use App\Wallet;
use Firebase\JWT\JWT;
use Hashids\Hashids;
use Illuminate\Http\Request;

class OTPController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

    public function smsOTP(Request $request)
    {
        if (!$request->header('app'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your app header'
            );
        if (!$request->header('typeApp'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your typeApp header'
            );
        if (!$request->header('typeAppChild'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your typeAppChild header'
            );
        $phone = $this->help->phoneChecker($request->input('mobile'), $request->input('country'));
        return $this->sendSMS($request, $phone);
    }

    public function verifyOTP(Request $request)
    {
        if (!$request->header('app'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your app header'
            );
        if (!$request->header('typeApp'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your typeApp header'
            );
        if (!$request->header('typeAppChild'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your typeAppChild header'
            );
        if (!$request->header('agent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'Plz check your agent'
            );
        if (!$request->input('code'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'Plz check your code'
            );
        $phone = $this->help->phoneChecker($request->input('mobile'), $request->input('country'));
        if (!$this->CheckUsersLoginToken($phone, $request->input('code')))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'code isn`t true'
            );
        $app = App::join(Constants::APPS_SETTING_DB, Constants::APP_DB . '.id', '=', Constants::APPS_SETTING_DB . '.app_id')
            ->where([
                Constants::APP_DB . '.app' => $request->header('app'),
                Constants::APP_DB . '.type_app' => $request->header('typeApp'),
                Constants::APP_DB . '.type_app_child' => $request->header('typeAppChild')
            ])->first();
        if (!$app)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your app & typeApp & typeAppChild header'
            );
        return $this->respond($this->verify($phone, $request, $app));
    }


    ////////////////////private function///////////////////////

    private function sendSMS($request, $phone)
    {
        $token = rand(1000, 9999);
        if ($request->header('X-DEBUG') == 1)
            $token = 1010;
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
//            throw new ApiException(
//                ApiException::EXCEPTION_NOT_FOUND_404,
//                "too many request"
//            );
//        }
        $app = App::join(Constants::APPS_SETTING_DB, Constants::APP_DB . '.id', '=', Constants::APPS_SETTING_DB . '.app_id')
            ->where([
                Constants::APP_DB . '.app' => $request->header('app'),
                Constants::APP_DB . '.type_app' => $request->header('typeApp'),
                Constants::APP_DB . '.type_app_child' => $request->header('typeAppChild')
            ])->first();
        if (!$app)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your app & typeApp & typeAppChild header'
            );
        switch ($app->type_sms) {
            case "kavenegar":
                $result = $this->callKavenegarApi([
                    "receptor" => "00" . $phone,
                    "type" => "sms",
                    "template" => json_decode($app->info_sms)->otp_template,
                    "token" => $token
                ], json_decode($app->info_sms)->kavenegar_api_key);
                if ($result["return"]["status"] != 200)
                    throw new ApiException(
                        ApiException::EXCEPTION_NOT_FOUND_404,
                        $result["return"]["message"]
                    );
                return ["status" => "success"];
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    "Plz check your app setting"
                );
        }
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

    private function verify($phone, $request, $app)
    {
        $user = User::where(['phone' => $phone])->first();
        if (!$user) {
            $hashIds = new Hashids($app->type_app);
            $refLink = $hashIds->encode($phone, intval(microtime(true)));
            $user = User::create([
                'phone' => $phone,
                'email' => '',
                'password' => '',
                'gmail' => '',
                'name' => $request->input('name'),
                'image' => '',
                'gender' => 'مرد',
                "ref_link" => $refLink,
                'info' => '',
                'remember_token' => '',
            ]);
            $userApps = UserApps::create([
                'user_id' => $user->id,
                'app_id' => $app->id,
                'activated' => 1,
                'created_at' => strtotime(date('Y-m-d'))
            ]);
            $user->user_apps = $userApps;
            $user->app = $app;
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'price' => 0,
            ]);
            $user->wallet = $wallet;
        }
        $user->wallet = Wallet::where(['user_id' => $user->id])->first();
        if (!$userApps = UserApps::where(['user_id' => $user->id, 'app_id' => $app->id])->first()) {
            $userApps = UserApps::create([
                'user_id' => $user->id,
                'app_id' => $app->id,
                'activated' => 1,
                'created_at' => strtotime(date('Y-m-d'))
            ]);
            if ($userRef = User::where(['ref_link' => $request->input('refLink')])->where("phone", "!=", $phone)->first())
                if (!UsersRefer::where(['user_id' => $userRef->id, 'ref_user_id' => $user->id])->exists())
                    UsersRefer::create(['user_id' => $userRef->id, 'ref_user_id' => $user->id, 'app_id' => $app->id, 'type' => Constants::LOGIN_TYPE_SMS, 'created_at' => strtotime("now")]);
        }
        $user->user_apps = $userApps;
        $user->app = $app;
        if ($user->user_apps->activated != 1)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'your are`t activated'
            );
        $this->generateToken($user, $request->header('agent'));
        UsersLoginToken::where(['login' => $phone, 'token' => $request->input('code')])->delete();
        return $user;
    }

    private function generateToken($user, $agent)
    {
        $object = array(
            "user_id" => $user->id,
            "agent" => $agent,
            "app_id" => $user->user_apps->id,
            "app" => $user->app->app,
            "type_app" => $user->app->type_app,
            "type_app_child" => $user->app->type_app_child,
        );
        $token = JWT::encode($object, config("jwt.secret"));
        $user->token = $token;
        return true;
    }
}
