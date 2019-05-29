<?php


namespace App\Inside;

use App\Shopping;

class Helpers
{
    public function priceNumberDigitsToNormal($price)
    {
        $price = str_replace(',', '', $price);
        return $this->normalizePhoneNumber($price);
    }

    public function phoneChecker($phone, $country = "IR")
    {
        if (!$country)
            $country = "IR";
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumber = $phoneUtil->parse($phone, $country);
        $phone = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
        $phone = str_replace('+', '', $phone);
        return $this->normalizePhoneNumber($phone);
    }

    public function normalizePhoneNumber($phone)
    {
        $newNumbers = range(0, 9);
        $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $string = str_replace($arabic, $newNumbers, $phone);
        $string = str_replace($persian, $newNumbers, $string);
        $string = str_replace(' ', '', $string);
        return $string;
    }

    public function getScaledDimension($w, $h, $requiredWidth, $requiredHeight, $inflate)
    {
        if ($w == 0 || $h == 0) {
            return [$w, $h];
        }

        $newWidth = $w;
        $newHeight = $h;

        if ($w > $requiredWidth || $inflate && $w < $requiredWidth) {
            //scale width to fit
            $newWidth = $requiredWidth;
            //scale height to maintain aspect ratio
            $newHeight = $newWidth * $h / $w;
        }

        if ($newHeight > $requiredHeight) {
            //scale height to fit instead
            $newHeight = $requiredHeight;
            //scale width to maintain aspect ratio
            $newWidth = $newHeight * $w / $h;
        }

        return [$newWidth, $newHeight];
    }

    public function voucher($appName)
    {
        $voucher = Shopping::count();
        $zero = '';
        if (strlen($voucher) < 8)
            for ($i = strlen($voucher); $i < 8; $i++)
                $zero = $zero . '0';
        return substr($appName, 0, 1) . "-" . $zero . $voucher;
    }

    public function base64url_encode($plainText)
    {
        $base64 = base64_encode($plainText);
        $base64url = strtr($base64, '+/=', '-_,');
        return $base64url;
    }

    public function base64url_decode($plainText)
    {
        $base64url = strtr($plainText, '-_,', '+/=');
        $base64 = base64_decode($base64url);
        return $base64;
    }
}
