<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\User;
use App\UserApps;
use App\UserStickerPack;
use App\Wallet;
use Hashids\Hashids;
use Illuminate\Http\Request;

class VasController extends ApiController
{
    public function postSmsRequest(Request $request)
    {
        $user = User::where("id", $request->input("user_id"))->first();
        $phone = $user->phone;
        $re = '/(\0)?([ ]|,|-|[()]){0,2}9[0|1|2|3|4|9]([ ]|,|-|[()]){0,2}(?:[0-9]([ ]|,|-|[()]){0,2}){8}/m';
        $str = $phone;
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        if (!$matches)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'your phone number is wrong'
            );
        $phone = '0' . $matches[0][0];
        $phone = str_replace('+', '', $phone);
        $phone = $this->normalizePhoneNumber($phone);
        $result = $this->callApiSubscribe($phone);
        if ($result != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "error"
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
        $user = User::where("id", $request->input("user_id"))->first();
        $phone = $user->phone;
        $re = '/(\0)?([ ]|,|-|[()]){0,2}9[0|1|2|3|4|9]([ ]|,|-|[()]){0,2}(?:[0-9]([ ]|,|-|[()]){0,2}){8}/m';
        $str = $phone;
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        if (!$matches)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'your phone number is wrong'
            );
        $phone = '0' . $matches[0][0];
        $phone = str_replace('+', '', $phone);
        $phone = $this->normalizePhoneNumber($phone);
        $result = $this->callApiVerifySubscribe($phone, $request->input('code'));
        if ($result != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "your code is wrong"
            );
        return $this->respond($this->verify($request->input("user_id")));
    }

    private function callApiSubscribe($phone)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://79.175.174.114:8090/vas/backend/web/api-gateway/subscribe?username=" . config("vas.username") . "&password=" . config("vas.password") . "&serviceId=" . config("vas.serviceId") . "&number=" . $phone);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: */*'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info ["http_code"];
    }

    private function callApiVerifySubscribe($phone, $code)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://79.175.174.114:8090/vas/backend/web/api-gateway/confirm?username=" . config("vas.username") . "&password=" . config("vas.password") . "&serviceId=" . config("vas.serviceId") . "&number=" . $phone . "&otp=" . $code);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: */*'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info ["http_code"];
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

    private function verify($userId)
    {
        User::where(['id' => $userId])->update(['profile_type' => Constants::PROFILE_TYPE_SUPERUSER]);
        return ["status" => "success"];
    }


    ///////////////////// Before Login/////////////////////////

    public function postSmsRequestBeforeLogin(Request $request)
    {
        if (!$request->input('mobile'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'plz check your mobile'
            );
        $phone = $request->input('mobile');
        $re = '/(\0)?([ ]|,|-|[()]){0,2}9[0|1|2|3|4|9]([ ]|,|-|[()]){0,2}(?:[0-9]([ ]|,|-|[()]){0,2}){8}/m';
        $str = $phone;
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        if (!$matches)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'your phone number is wrong'
            );
        $phone = '0' . $matches[0][0];
        $phone = str_replace('+', '', $phone);
        $phone = $this->normalizePhoneNumber($phone);
        $result = $this->callApiSubscribe($phone);
        if ($result != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "error"
            );
        return $this->respond(["status" => "success"], null);
    }


    public function postVerifyRequestBeforeLogin(Request $request)
    {
        if (!$request->input('code'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your code'
            );
        if (!$request->input('mobile'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'plz check your mobile'
            );
        $phone = $request->input('mobile');
        $re = '/(\0)?([ ]|,|-|[()]){0,2}9[0|1|2|3|4|9]([ ]|,|-|[()]){0,2}(?:[0-9]([ ]|,|-|[()]){0,2}){8}/m';
        $str = $phone;
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        if (!$matches)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'your phone number is wrong'
            );
        $phone = '0' . $matches[0][0];
        $phone = str_replace('+', '', $phone);
        $phone = $this->normalizePhoneNumber($phone);
        $result = $this->callApiVerifySubscribe($phone, $request->input('code'));
        if ($result != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "your code is wrong"
            );
        $this->verifyRegister($phone);
        return $this->respond(["status" => "success"]);
    }

    private function verifyRegister($phone)
    {
        $user = User::where([Constants::USERS_DB . '.active' => 1, Constants::USERS_DB . '.phone' => $phone])
            ->select(
                Constants::USERS_DB . '.id',
                Constants::USERS_DB . '.phone',
                Constants::USERS_DB . '.email',
                Constants::USERS_DB . '.username',
                Constants::USERS_DB . '.active',
                Constants::USERS_DB . '.auto_charge',
                Constants::USERS_DB . '.first_name',
                Constants::USERS_DB . '.last_name',
                Constants::USERS_DB . '.birthday',
                Constants::USERS_DB . '.bio',
                Constants::USERS_DB . '.gender',
                Constants::USERS_DB . '.ref_link',
                Constants::USERS_DB . '.created_at',
                Constants::USERS_DB . '.updated_at'
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
            $info = UserApps::create(['user_id' => $user->id, 'type_app' => Constants::TYPE_APP_ARIOO, 'created_at' => strtotime(date('Y-m-d'))]);
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
        }
        User::where(['id' => $user->id])->update(['profile_type' => Constants::PROFILE_TYPE_SUPERUSER]);
    }

}
