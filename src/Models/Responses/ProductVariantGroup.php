<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class ProductVariantGroup extends BaseModel
{
    /** @var string */
    public $name;

    /** @var ProductVariant[] */
    public $variants;

    public function __construct(array $abstract)
    {
        parent::__construct($abstract);
    }
}
