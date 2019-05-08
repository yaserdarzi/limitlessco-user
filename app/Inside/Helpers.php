<?php


namespace App\Inside;

use App\Exceptions\ApiException;

class Helpers
{
    public function phoneChecker($phone)
    {
        $re = '/(\0)?([ ]|,|-|[()]){0,2}9[0|1|2|3|4|9]([ ]|,|-|[()]){0,2}(?:[0-9]([ ]|,|-|[()]){0,2}){8}/m';
        $str = $phone;
        preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        if (!$matches)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'your phone number is wrong'
            );
        $phone = '98' . $matches[0][0];
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