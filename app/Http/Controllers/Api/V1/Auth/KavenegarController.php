<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Contact;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Tasks;
use App\User;
use App\UserApps;
use App\UsersLoginToken;
use App\UsersLoginTokenLog;
use App\UsersPointsHistory;
use App\UsersRefer;
use App\UserStickerPack;
use App\Wallet;
use checkmobi\CheckMobiRest;
use Firebase\JWT\JWT;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class KavenegarController extends ApiController
{
    public function postSmsRequest(Request $request)
    {
        $country = $request->input('country');
        if (!$request->input('country'))
            $country = "IR";
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumber = $phoneUtil->parse($request->input('mobile'), $country);
        $phone = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
        $phone = str_replace('+', '', $phone);
        if ($country)
            return $this->sendSMS($request, $phone);
        $api = new CheckMobiRest(config("config.checkMobiToken"));
        $response = $api->RequestValidation(array("type" => "sms", "number" => "+" . $phone));
        if (!$this->UsersLoginToken($phone, $response['response']['id'], Constants::LOGIN_TYPE_SMS))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'too many request'
            );
        return $this->respond(["status" => "success"]);
    }

    public function postCallRequest(Request $request)
    {
        $country = $request->input('country');
        if (!$request->input('country'))
            $country = "IR";
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumber = $phoneUtil->parse($request->input('mobile'), $country);
        $phone = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
        $phone = str_replace('+', '', $phone);
        if ($country)
            return $this->sendCall($request, $phone);
        $api = new CheckMobiRest(config("config.checkMobiToken"));
        $response = $api->RequestValidation(array("type" => "cli", "number" => "+" . $phone));
        if (!$this->UsersLoginToken($phone, $response['response']['id'], Constants::LOGIN_TYPE_SMS))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'too many request'
            );
        return $this->respond(["status" => "success"]);
    }

    public function postVerifyRequest(Request $request)
    {
        if (!$request->header('uuid'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'Plz check your uuid'
            );
        if (!$request->header('app'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'Plz check your app'
            );
        if (!$request->input('code'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'Plz check your code'
            );
        if (!$request->header('agent'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'Plz check your agent'
            );
        $country = $request->input('country');
        if (!$request->input('country'))
            $country = "IR";
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumber = $phoneUtil->parse($request->input('mobile'), $country);
        $phone = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
        $phone = str_replace('+', '', $phone);
        if ($country)
            return $this->verifyIR($request, $phone);
        $api = new CheckMobiRest(config("config.checkMobiToken"));
        $phone = $this->normalizePhoneNumber($phone);
        $id = $this->getIdLoginToken($phone);
        if (!$id)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'code isn`t true'
            );
        $response = $api->VerifyPin(array("id" => $id, "pin" => $request->input('code')));
        if (!$response['response']['validated'])
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'code isn`t true'
            );
        return $this->respond($this->verify($phone, $request->header('agent'), $request->header('app'), $request->header('uuid'), $request->input("refLink")));
    }


    ////////////////////private function///////////////////////

    private function sendSMS($request, $phone)
    {
        $phone = $this->normalizePhoneNumber($phone);
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
//            throw new ApiException(
//                ApiException::EXCEPTION_NOT_FOUND_404,
//                "too many request"
//            );
//        }
        $result = $this->callKavenegarApi([
            "receptor" => "00" . $phone,
            "type" => "sms",
            "template" => config("OtpConfig.OTP_template"),
            "token" => $token
        ]);
        if ($result["return"]["status"] != 200)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                $result["return"]["message"]
            );
        return ["status" => "success"];
    }

    private function sendCall($request, $phone)
    {
        $phone = $this->normalizePhoneNumber($phone);
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
//            throw new ApiException(
//                ApiException::EXCEPTION_NOT_FOUND_404,
//                "too many request"
//            );
//        }
        $result = $this->callKavenegarApi([
            "receptor" => "00" . $phone,
            "type" => "call",
            "template" => config("OtpConfig.OTP_template"),
            "token" => $token
        ]);
        if (isset($result['error']) != null)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                $result['error']
            );
        return ["status" => "success"];
    }

    private function verifyIR($request, $phone)
    {
        $phone = $this->normalizePhoneNumber($phone);
        if (!$this->CheckUsersLoginToken($phone, $request->input('code')))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'code isn`t true'
            );
        return $this->respond($this->verify($phone, $request->header('agent'), $request->header('app'), $request->header('uuid'), $request->input("refLink")));
    }

    private function callKavenegarApi($req)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.kavenegar.com/v1/" . config("OtpConfig.Kavenegar_api_key") . "/verify/lookup.json");
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

    private function normalizePhoneNumber($phone)
    {
        $newNumbers = range(0, 9);
        $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $string = str_replace($arabic, $newNumbers, $phone);
        $string = str_replace($persian, $newNumbers, $string);
        $string = str_replace(' ', '', $string);
        return $string;
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
            UsersLoginToken::where(['login' => $phone, 'token' => $token])->delete();
            return true;
        } else
            return false;
    }

    private function getIdLoginToken($phone)
    {
        UsersLoginToken::where('expire_at', '<', strtotime(date('Y-m-d H:i:s')))->delete();
        $UsersLoginToken = UsersLoginToken::where(['login' => $phone])->first();
        if ($UsersLoginToken) {
            UsersLoginToken::where(['login' => $phone])->delete();
            return $UsersLoginToken->token;
        } else
            return null;
    }


    private function verify($phone, $agent, $app, $uuid, $refLinkPoint)
    {
        $user = User::join(Constants::USERS_APPS_DB, Constants::USERS_DB . '.id', '=', Constants::USERS_APPS_DB . '.user_id')
            ->where([Constants::USERS_APPS_DB . '.type_app' => $app, Constants::USERS_DB . '.phone' => $phone])
            ->select(
                Constants::USERS_DB . '.id',
                Constants::USERS_DB . '.phone',
                Constants::USERS_DB . '.email',
                Constants::USERS_DB . '.username',
                Constants::USERS_DB . '.active',
                Constants::USERS_DB . '.user_level',
                Constants::USERS_DB . '.auto_charge',
                Constants::USERS_DB . '.first_name',
                Constants::USERS_DB . '.last_name',
                Constants::USERS_DB . '.birthday',
                Constants::USERS_DB . '.bio',
                Constants::USERS_DB . '.profile_type',
                Constants::USERS_DB . '.gender',
                Constants::USERS_DB . '.ref_link',
                Constants::USERS_DB . '.created_at',
                Constants::USERS_DB . '.updated_at',
                Constants::USERS_APPS_DB . '.type_app'
            )
            ->first();
        if (!$user) {
            $hashIds = new Hashids("arioo");
            $refLink = $hashIds->encode($phone, intval(microtime(true)));
            $user = User::create(['phone' => $phone, 'email' => '', 'auto_charge' => 0, 'active' => 1, 'user_level' => 0,
                'remember_token' => '', 'first_name' => '', 'birthday' => 0, 'bio' => '', 'username' => '', 'profile_type' => 0, 'gender' => 0, 'last_name' => '', "ref_link" => $refLink]);
            UserStickerPack::create([
                'user_id' => $user->id,
                'sticker_pack_id' => 1,
                'created_at' => strtotime(date('Y-m-d H:i:s'))
            ]);
            $info = UserApps::create(['user_id' => $user->id, 'type_app' => $app, 'created_at' => strtotime(date('Y-m-d'))]);
            $user->type_app = $info->type_app;
            $user->media_id = 0;
            //create default wallet
            $walletHandle = Constants::TYPE_APP_ARIOO_INT . "x" . hexdec(crc32($user->id));
            Wallet::create([
                'user_id' => $user->id,
                'title' => Constants::MAIN_DEFAULT_WALLET_TITLE,
                'wallet_handle' => $walletHandle,
                'is_default' => Constants::MAIN_DEFAULT_WALLET_VALUE,
                'price' => Constants::MAIN_DEFAULT_WALLET_PRICE,
            ]);
            if ($userRef = User::join(Constants::USERS_APPS_DB, Constants::USERS_DB . '.id', '=', Constants::USERS_APPS_DB . '.user_id')->where([Constants::USERS_APPS_DB . '.type_app' => $app, Constants::USERS_DB . '.active' => 1, Constants::USERS_DB . '.ref_link' => $refLinkPoint])->where(Constants::USERS_DB . ".phone", "!=", $phone)->first())
                if (!UsersRefer::where(['user_id' => $userRef->user_id, 'ref_user_id' => $user->id])->exists()) {
                    $task = Tasks::where(['title' => "link", 'category' => "invite"])->first();
                    if ($task) {
                        UsersPointsHistory::create([
                            'user_id' => $userRef->user_id,
                            'task_id' => $task->id,
                            'type' => $task->title,
                            'point' => $task->point,
                            'payload' => $task->toArray(),
                            'created_at' => strtotime("now")
                        ]);
                        UsersRefer::create(['user_id' => $userRef->user_id, 'ref_user_id' => $user->id, 'type' => Constants::LOGIN_TYPE_SMS, 'created_at' => strtotime("now")]);
                    }
                }
        }
        if ($user->active != 1)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'your are`t active'
            );
        if ($user->first_name == '' && $user->last_name == '')
            $user->isFirst = true;
        else
            $user->isFirst = false;
        $user->photo = $user->photo;
        $user->media_id = $user->media_id;
        $this->generateToken($user, $agent, $app);

        // Hamed - disabling queue and exchange creation from api for now
        /* create queue for user device and create exchange for user */
        /*$connection = new AMQPStreamConnection(config("rabbitmq.server"), config("rabbitmq.port"), config("rabbitmq.user"), config("rabbitmq.password"), '/');
        $channel = $connection->channel();
        $channel->exchange_declare('ex.u.' . $user->id, 'topic', false, true, false);
        $channel->queue_declare('u.' . $user->id . '.' . $uuid, false, true, false, false);
        $channel->queue_bind('u.' . $user->id . '.' . $uuid, 'ex.u.' . $user->id);
        $channel->close();
        $connection->close();*/
        /* create queue for user device and create exchange for user */
        $sync = Contact::where('user_id', $user->id)->count();
        if ($sync == 0)
            $user['sync_contact'] = false;
        elseif ($sync != 0)
            $user['sync_contact'] = true;
        return $user;
    }

    private function generateToken(User $user, $agent, $app)
    {
        $object = array(
            "user_id" => $user->id,
            "agent" => $agent,
            "app" => $app
        );
        $token = JWT::encode($object, config("jwt.secret"));
        $user->token = $token;
        return true;
    }
}
