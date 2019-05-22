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
    const USERS_APPS_DB = 'user_apps';
    const USERS_REFER_DB = 'users_refer';
    const WALLET_DB = 'wallet';
    const WALLET_INVOICE_DB = 'wallet_invoice';
    const SUPPLIER_DB = 'supplier';
    const SUPPLIER_USERS_DB = 'supplier_users';
    const SUPPLIER_APP_DB = 'supplier_app';
    const AGENCY_DB = 'agency';
    const AGENCY_USERS_DB = 'agency_users';
    const AGENCY_APP_DB = 'agency_app';

    //sms log type
    Const LOGIN_TYPE_SMS = "login_with_sms";
    Const LOGIN_TYPE_CALL = "login_with_call";
    Const LOGIN_TYPE_EMAIL = "login_with_email";
    Const LOGIN_TYPE_GMAIL = "login_with_gmail";

    //Market
    Const MARKET_ZARINPAL = "zarinpal";

    //SMS
    Const SMS_KAVENEGAR = "kavenegar";

    //Status
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_DEACTIVATE = 'deactivate';

    //Role
    const ROLE_ADMIN = 'admin';
    const ROLE_SALES_MAN = 'sales_man';
    const ROLE_COUNTER_MAN = 'counter_man';

}
