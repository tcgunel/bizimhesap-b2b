<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use TCGunel\BizimHesapB2b\Models\Responses\AbstractUser;

class BizimHesapB2bAbstracts extends BizimHesapB2b
{
    use HandleErrors;

    protected $endpoint = "https://bizimhesap.com/api/b2b/abstract/{customer-id}";

    protected $customer_id;

    /**
     * BizimHesapB2bAbstracts constructor.
     * @param Http|null http_client
     * @param string $token
     * @param string $customer_id
     */
    public function __construct($http_client, string $token, string $customer_id)
    {
        parent::__construct($http_client, $token);

        $this->setCustomerId($customer_id);
    }

    /**
     * @param string $customer_id
     * @return BizimHesapB2bAbstracts
     */
    public function setCustomerId(string $customer_id): BizimHesapB2bAbstracts
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * @param string $endpoint
     * @return BizimHesapB2bAbstracts
     */
    public function setEndpoint(string $endpoint): BizimHesapB2bAbstracts
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return AbstractUser
     * @throws Exceptions\BizimHesapB2bException
     * @throws RequestException
     */
    public function get()
    {
        $this->endpoint = strtr($this->endpoint, ["{customer-id}" => $this->customer_id]);

        $response = $this->http_client::withHeaders([
            'token' => $this->token,
        ])->get($this->endpoint);

        $this->checkForErrors($response);

        if ($response->successful()) {

            $result = $this->collect($response->body());

            if ($result->has("abstractUser")) {

                $result->put("abstractUser", new AbstractUser($result->get("abstractUser")));

                return $result->get("abstractUser");

            }

        }

        return $result;
    }

    protected function collect(string $response): Collection
    {
        $collected = collect(json_decode($response, true));

        if ($collected->has("data")) {

            $collected->put("abstractUser", collect($collected->get("data")));

        }

        $collected->offsetUnset("data");

        return $collected;
    }
}
