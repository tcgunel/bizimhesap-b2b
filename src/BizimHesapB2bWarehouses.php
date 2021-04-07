<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use TCGunel\BizimHesapB2b\Models\Responses\Warehouse;

class BizimHesapB2bWarehouses extends BizimHesapB2b
{
    use HandleErrors;

    protected $endpoint = "https://bizimhesap.com/api/b2b/warehouses";

    /**
     * BizimHesapB2bWarehouses constructor.
     * @param Http|null http_client
     * @param string $token
     */
    public function __construct($http_client, string $token)
    {
        parent::__construct($http_client, $token);
    }

    /**
     * @param string $endpoint
     * @return BizimHesapB2bWarehouses
     */
    public function setEndpoint(string $endpoint): BizimHesapB2bWarehouses
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return Collection|Warehouse[]
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

            if ($result->has("warehouses")) {

                $result->put("warehouses", $result->get("warehouses")->mapInto(Warehouse::class));

                return $result->get("warehouses")->toArray();

            }

        }

        return $result;
    }

    protected function collect(string $response): Collection
    {
        $collected = collect(json_decode($response, true));

        if (isset($collected->get("data")["warehouses"])) {

            $collected->put("warehouses", collect($collected->get("data")["warehouses"]));

        }

        $collected->offsetUnset("data");

        return $collected;
    }
}
