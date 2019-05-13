<?php


namespace App\Inside;

class Constants
{
    //Database
    const USERS_DB = 'users';
    const PASSWORD_RESETS_DB = 'password_resets';
    const USERS_LOGIN_TOKEN_DB = 'users_login_token';
    const USERS_LOGIN_TOKEN_LOG_DB = 'users_login_token_log';
    const APP_DB = 'app';
    const APPS_SETTING_DB = 'user_setting';
    const USERS_APPS_DB = 'user_apps';
    const USERS_REFER_DB = 'users_refer';
    const WALLET_DB = 'wallet';
    const WALLET_INVOICE_DB = 'wallet_invoice';

    //sms log type
    Const LOGIN_TYPE_SMS = "login_with_sms";
    Const LOGIN_TYPE_CALL = "login_with_call";
    Const LOGIN_TYPE_EMAIL = "login_with_email";
    Const LOGIN_TYPE_GMAIL = "login_with_gmail";

    //Market
    Const MARKET_ZARINPAL = "zarinpal";

    //SMS
    Const SMS_KAVENEGAR = "kavenegar";


}
