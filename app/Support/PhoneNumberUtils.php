<?php

namespace App\Support;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\NumberParseException;

class PhoneNumberUtils
{
    public static function isValidNumber($number)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $phoneUtil->parse($number, "CN"); // 这里以中国为示例国家代码
            return $phoneUtil->isValidNumber($phoneNumber);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    public static function getPhoneModelWithCountry($number)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $phoneUtil->parse($number, "CN"); // 这里以中国为示例国家代码
            $phoneModel = new \stdClass();
            $phoneModel->countryCode = $phoneNumber->getCountryCode();
            $phoneModel->nationalNumber = $phoneNumber->getNationalNumber();
            return $phoneModel;
        } catch (NumberParseException $e) {
            return null;
        }
    }
}
