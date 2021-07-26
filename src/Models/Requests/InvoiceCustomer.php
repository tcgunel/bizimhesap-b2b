<?php

namespace TCGunel\BizimHesapB2b\Models\Requests;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\RegionCode;

class InvoiceCustomer
{
    /** @var integer */
    public $customerId;

    /** @var string */
    public $title;

    /** @var string */
    public $taxOffice;

    /** @var string */
    public $taxNo;

    /** @var float */
    public $email;

    /** @var string */
    public $phone;

    /** @var string */
    public $address;

    public function __toArray()
    {
        return call_user_func('get_object_vars', $this);
    }

    public function setPhone($phone)
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        $parsed = $phoneNumberUtil->parseAndKeepRawInput($phone, RegionCode::GB);

        $phone = $parsed->getNationalNumber();

        $this->phone = $phone;
    }
}
