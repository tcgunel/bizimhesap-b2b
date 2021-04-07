<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class _Abstract extends BaseModel
{
    /** @var string */
    public $trxdate;

    /** @var string */
    public $type;

    /** @var string */
    public $note;

    /** @var string */
    public $payment;

    /** @var string */
    public $debit;

    /** @var string */
    public $credit;

    /** @var string */
    public $balance;

    public function __construct(array $abstract)
    {
        parent::__construct($abstract);
    }
}
