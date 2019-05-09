<?php


namespace App\Inside;

class Helpers
{
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

    ////////////////private function///////////////

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
}
