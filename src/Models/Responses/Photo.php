<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class Photo extends BaseModel
{
    /** @var string */
    public $is_cover;

    /** @var string */
    public $url;

    public function __construct(array $photo)
    {
        parent::__construct($photo);
    }
}
