<?php

namespace TCGunel\BizimHesapB2b;


use Illuminate\Support\Facades\Http;

class BizimHesapB2b
{
    /** @var Http */
    protected $http_client;

    /** @var string */
    protected $token;

    /**
     * BizimHesapB2b constructor.
     * @param Http|null $http_client
     * @param string $token
     */
    public function __construct($http_client, string $token)
    {
        if ($http_client instanceof Http === false) {

            $this->http_client = Http::class;

        } else {

            $this->http_client = $http_client;

        }

        $this->token = $token;
    }

}
