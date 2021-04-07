<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class Warehouse extends BaseModel
{
    /** @var string */
    public $id;

    /** @var string */
    public $title;

    public function __construct(array $abstract)
    {
        parent::__construct($abstract);
    }
}
