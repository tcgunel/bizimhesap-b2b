<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use TCGunel\BizimHesapB2b\Models\Responses\Customer;

class BizimHesapB2bCustomers extends BizimHesapB2b
{
    use HandleErrors;

    protected $endpoint = "https://bizimhesap.com/api/b2b/customers";

    /**
     * BizimHesapB2bCustomers constructor.
     * @param Http|null http_client
     * @param string $token
     */
    public function __construct($http_client, string $token)
    {
        parent::__construct($http_client, $token);
    }

    /**
     * @param string $endpoint
     * @return BizimHesapB2bCustomers
     */
    public function setEndpoint(string $endpoint): BizimHesapB2bCustomers
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return Collection|Customer[]
     * @throws Exceptions\BizimHesapB2bException
     * @throws RequestException
     */
    public function get()
    {
        $response = $this->http_client::withHeaders([
            'token' => $this->token,
        ])->get($this->endpoint);

        $this->checkForErrors($response);

        if ($response->successful()) {

            $result = $this->collect($response->body());

            if ($result->has("customers")) {

                $result->put("customers", $result->get("customers")->mapInto(Customer::class));

                return $result->get("customers")->toArray();

            }

        }

        return $result;
    }

    protected function collect(string $response): Collection
    {
        $collected = collect(json_decode($response, true));

        if (isset($collected->get("data")["customers"])) {

            $collected->put("customers", collect($collected->get("data")["customers"]));

        }

        $collected->offsetUnset("data");

        return $collected;
    }
}
