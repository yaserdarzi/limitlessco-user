<?php


namespace App\Inside;

class Constants
{
    //App Name
    const APP_NAME_HOTEL = "hotel";

    //Database Connection
    const CONNECTION_HOTEL = "pgsql_hotel";

    //Database Hotel
    const APP_HOTEL_DB_HOTEL_DB = 'hotel';
    const APP_HOTEL_DB_HOTEL_SUPPLIER_DB = 'hotel_supplier';
    const APP_HOTEL_DB_HOTEL_GALLERY_DB = 'hotel_gallery';
    const APP_HOTEL_DB_HOTEL_TOOLS_DB = 'hotel_tools';
    const APP_HOTEL_DB_HOTEL_DISTANCE_DB = 'hotel_distance';
    const APP_HOTEL_DB_ROOM_DB = 'room';
    const APP_HOTEL_DB_ROOM_GALLERY_DB = 'room_gallery';
    const APP_HOTEL_DB_ROOM_TOOLS_DB = 'room_tools';
    const APP_HOTEL_DB_ROOM_EPISODE_DB = 'room_episode';

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
    const AGENCY_WALLET_DB = 'agency_wallet';
    const AGENCY_WALLET_INVOICE_DB = 'agency_wallet_invoice';
    const SALES_DB = 'sales';
    const SUPPLIER_SALES_DB = 'supplier_sales';
    const SHOPPING_DB = 'shopping';
    const SHOPPING_BAG_DB = 'shopping_bag';
    const SHOPPING_INVOICE_DB = 'shopping_invoice';

    //Sales Type
    const SALES_TYPE_API = 'api';
    const SALES_TYPE_JUSTKISH = 'justkish';
    const SALES_TYPE_AGENCY = 'agency';
    const SALES_TYPE_PERCENT_SITE = 'percent_site';
    const SALES_TYPE_SEPEHR = 'sepehr';

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

    //Type Price
    const TYPE_PERCENT = "percent";
    const TYPE_PRICE = "price";

    //Shopping Status
    const SHOPPING_STATUS_SHOPPING = "shopping";
    const SHOPPING_STATUS_PAYMENT = "payment";
    const SHOPPING_STATUS_SUCCESS = "success";
    const SHOPPING_STATUS_FINISH = "finish";
    const SHOPPING_STATUS_RETURN = "return";
}
