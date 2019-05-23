<?php


namespace App\Inside;

class Helpers
{
    public function priceNumberDigitsToNormal($price)
    {
        $price = str_replace(',', '', $price);
        return $this->normalizePhoneNumber($price);
    }

    public function phoneChecker($phone, $country)
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
}
