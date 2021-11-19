<?php

namespace TCGunel\BizimHesapB2b;

use TCGunel\BizimHesapB2b\Exceptions\BizimHesapB2bException;

trait HandleErrors
{
    /**
     * @param \Illuminate\Http\Client\Response $response
     * @throws BizimHesapB2bException
     */
    protected function checkForErrors($response)
    {
        $body = collect(json_decode($response->body(), true));

        $error = "";
        if ($body->get("resultCode") === 0) {

            $error = $body->get("errorText");

        }

        if ($body->has("Message")) {

            $error = $body->get("Message");

        }

        if ($body->has("error") && !empty($body->get("error"))) {

            $error = $body->get("error");

        }

        if (!empty($error)) {

            throw new BizimHesapB2bException($error);

        }
    }
}
