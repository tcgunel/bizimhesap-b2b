<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class Customer extends BaseModel
{
    /** @var string */
    public $id;

    /** @var string */
    public $code;

    /** @var string */
    public $title;

    /** @var string */
    public $address;

    /** @var string */
    public $phone;

    /** @var string */
    public $taxno;

    /** @var string */
    public $taxoffice;

    /** @var string */
    public $authorized;

    /** @var string */
    public $balance;

    /** @var string */
    public $email;

    public function __construct(array $abstract)
    {
        parent::__construct($abstract);
    }
}
