<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class Product extends BaseModel
{
    /** @var string */
    public $id;

    /** @var string */
    public $code;

    /** @var string */
    public $barcode;

    /** @var string */
    public $title;

    /** @var float */
    public $price;

    /** @var string */
    public $currency;

    /** @var string */
    public $unit;

    /** @var float */
    public $tax;

    /** @var Photo[] */
    public $photo;

    /** @var string */
    public $description;

    /** @var string */
    public $ecommerceDescription;

    /** @var string */
    public $variant;

    /** @var float */
    public $quantity;

    /** @var string */
    public $brand;

    /** @var string */
    public $category;

    public function __construct(array $abstract)
    {
        parent::__construct($abstract);
    }
}
