<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class Invoice extends BaseModel
{
    /** @var string */
    public $url;

    /** @var string */
    public $guid;

    /** @var string */
    public $error;

    /** @var string */
    public $eInvoiceNo;

    public function __construct(array $abstract)
    {
        parent::__construct($abstract);
    }
}
