<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Support\Facades\Http;

class BizimHesapB2bCancelInvoice extends BizimHesapB2b
{
    use HandleErrors;

    protected $endpoint = "https://bizimhesap.com/api/b2b/cancelinvoice";

    protected $guid;

    /**
     * BizimHesapB2bCancelInvoice constructor.
     * @param Http|null http_client
     * @param string $token
     * @param string $guid
     * @throws Exceptions\BizimHesapB2bException
     */
    public function __construct($http_client, string $token, string $guid)
    {
        parent::__construct($http_client, $token);

        $this->guid = $guid;
    }

    /**
     * @param string $endpoint
     * @return BizimHesapB2bCancelInvoice
     */
    public function setEndpoint(string $endpoint): BizimHesapB2bCancelInvoice
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function run()
    {
        $response = $this->http_client::post($this->endpoint, [
            "FirmId" => $this->token,
            "Guid"   => $this->guid,
        ]);

        $this->checkForErrors($response);

        if ($response->successful()) {

            $result = collect(json_decode($response, true));

            return !$result->get("status");

        }

        return false;
    }
}
