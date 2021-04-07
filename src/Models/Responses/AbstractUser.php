<?php

namespace TCGunel\BizimHesapB2b\Models\Responses;

class AbstractUser
{
    /** @var string */
    public $title;

    /** @var string */
    public $email;

    /** @var string */
    public $phone;

    /** @var string */
    public $balance;

    /** @var string */
    public $debitSum;

    /** @var string */
    public $creditSum;

    /** @var string */
    public $link;

    /** @var _Abstract[] */
    public $abstract;

    public function __construct(object $abstract_user)
    {
        foreach ($abstract_user as $key => $arg) {

            if (property_exists($this, $key)) {

                $this->$key = $arg;

                if ($key === "abstract") {

                    $this->$key = collect($this->$key)->mapInto(_Abstract::class)->toArray();

                }

            }

        }

        return $this;
    }
}
