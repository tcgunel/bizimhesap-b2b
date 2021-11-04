<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class ProductVariant extends BaseModel
{
    /** @var string */
    public $name;

    /** @var float */
    public $price;

    /** @var string */
    public $code;

    /** @var string */
    public $barcode;

    public function __construct(array $abstract)
    {
        parent::__construct($abstract);
    }
}
