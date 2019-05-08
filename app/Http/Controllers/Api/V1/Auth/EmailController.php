<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\User;
use App\UsersLoginToken;
use App\UsersLoginTokenLog;
use Hashids\Hashids;
use Illuminate\Http\Request;
use \Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmailController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

    public function postLogin(Request $request)
    {
        if (!$request->header('agent'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your agent'
            );
        if (!filter_var($request->input("email"), FILTER_VALIDATE_EMAIL))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your email"
            );
        if (!$request->input('password'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your password'
            );
        if (!Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')]))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'Plz check your email and password'
            );
        $user = Auth::user();
        $object = array(
            "user_id" => $user->id,
            "agent" => $request->header('agent')
        );
        $token = JWT::encode($object, config("jwt.secret"));
        $user->token = $token;
        return $this->respond($user);
    }

    public function postRegister(Request $request)
    {
        if (!$request->header('agent'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your agent'
            );
        if (!filter_var($request->input("email"), FILTER_VALIDATE_EMAIL))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your email"
            );
        if (!$request->input('password'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your code'
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your name'
            );
        $phone = '';
        if ($request->input('phone')) {
            if (!$request->input('phone'))
                throw new ApiException(
                    ApiException::EXCEPTION_BAD_REQUEST_400,
                    'Plz check your phone'
                );
            $phone = $this->help->phoneChecker($request->input('phone'));
            if (User::where(['phone' => $phone])->exists())
                throw new ApiException(
                    ApiException::EXCEPTION_BAD_REQUEST_400,
                    'your phone is exists'
                );
        }
        $user = User::where(['email' => $request->input('email')])->first();
        if ($user)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'this email is exists'
            );
        $hashIds = new Hashids(config("config.hashIds"));
        $refLink = $hashIds->encode(intval($request->input('email')), intval(microtime(true)));
        $user = User::create([
            'phone' => $phone, 'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')), 'remember_token' => '',
            'gmail' => '', 'active' => 1, 'user_level' => '',
            'auto_charge' => 0, 'name' => $request->input('name'),
            'birthday' => 0, 'bio' => '', 'gender' => 0, "ref_link" => $refLink
        ]);
        $object = array(
            "user_id" => $user->id,
            "agent" => $request->header('agent')
        );
        $token = JWT::encode($object, config("jwt.secret"));
        $user->token = $token;
        return $this->respond($user);
    }


//---------------------------- Login by Google -------------------------------------

    public function listGoogleUser(Request $request)
    {
        if (!$request->header('uuid'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your uuid'
            );
        if (!$request->header('app'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your app'
            );
        if (!$request->header('agent'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your agent'
            );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $request->input('code')
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($resp);
        if (isset($resp->error_description))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'code isn`t true'
            );
        $user = User::join(Constants::USERS_APPS_DB, Constants::USERS_DB . '.id', '=', Constants::USERS_APPS_DB . '.user_id')
            ->join(Constants::USERS_GOOGLE, Constants::USERS_DB . '.id', '=', Constants::USERS_GOOGLE . '.user_id')
            ->where([Constants::USERS_APPS_DB . '.type_app' => $request->header('app'), Constants::USERS_DB . '.active' => 1, Constants::USERS_GOOGLE . '.google_id' => $resp->sub])
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
            $refLink = $hashIds->encode(intval($resp->email), intval(microtime(true)));
            $user = User::create(['email' => $resp->email, 'phone' => '', 'auto_charge' => 0, 'active' => 1, 'user_level' => 0,
                'remember_token' => '', 'first_name' => $resp->given_name, 'birthday' => 0, 'bio' => '', 'username' => '', 'profile_type' => 0, 'gender' => 0, 'last_name' => $resp->family_name, "ref_link" => $refLink]);
            UserStickerPack::create([
                'user_id' => $user->id,
                'sticker_pack_id' => 1,
                'created_at' => strtotime(date('Y-m-d H:i:s'))
            ]);
            $info = UserApps::create(['user_id' => $user->id, 'type_app' => $request->header('app'), 'created_at' => strtotime(date('Y-m-d'))]);
            $user->type_app = $info->type_app;
            //create default wallet
            $walletHandle = Constants::TYPE_APP_ARIOO_INT . "x" . hexdec(crc32($user->id));
            Wallet::create([
                'user_id' => $user->id,
                'title' => Constants::MAIN_DEFAULT_WALLET_TITLE,
                'wallet_handle' => $walletHandle,
                'is_default' => Constants::MAIN_DEFAULT_WALLET_VALUE,
                'price' => Constants::MAIN_DEFAULT_WALLET_PRICE,
            ]);
            UsersGoogle::create(['user_id' => $user->id, 'google_id' => $resp->sub, 'created_at' => strtotime(date('Y-m-d'))]);
            $user->photo = $resp->picture;
            $user->isFirst = true;
            if ($userRef = User::join(Constants::USERS_APPS_DB, Constants::USERS_DB . '.id', '=', Constants::USERS_APPS_DB . '.user_id')->where([Constants::USERS_APPS_DB . '.type_app' => $request->header("app"), Constants::USERS_DB . '.active' => 1, Constants::USERS_DB . '.ref_link' => $request->input("refLink")])->where(Constants::USERS_DB . ".email", "!=", $resp->email)->first())
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
                        UsersRefer::create(['user_id' => $userRef->user_id, 'ref_user_id' => $user->id, 'type' => Constants::LOGIN_TYPE_GMAIL, 'created_at' => strtotime("now")]);
                    }
                }
        } else {
            $user->isFirst = false;
            $user->photo = $user->photo;
        }
        $user->media_id = $user->media_id;
        $this->generateToken($user, $request->header('agent'), $request->header('app'));
        return $this->respond($user);

    }
}