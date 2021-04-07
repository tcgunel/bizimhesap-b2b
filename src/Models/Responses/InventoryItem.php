<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class InventoryItem extends BaseModel
{
    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** @var integer */
    public $qty;

    public function __construct(array $abstract)
    {
        parent::__construct($abstract);
    }
}
