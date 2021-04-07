<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Support\Facades\Http;

abstract class BizimHesapB2bClient
{
    public function __construct(?Http $client)
    {
        if ($client instanceof Http === false){

            return Http::class;

        }

        return $client;
    }
}
