<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use TCGunel\BizimHesapB2b\Models\Responses\InventoryItem;
use TCGunel\BizimHesapB2b\Models\Responses\Warehouse;

class BizimHesapB2bInventory extends BizimHesapB2b
{
    use HandleErrors;

    protected $endpoint = "https://bizimhesap.com/api/b2b/warehouses/{warehouse-id}";

    protected $warehouse_id;

    /**
     * BizimHesapB2bWarehouses constructor.
     * @param Http|null http_client
     * @param string $token
     * @param string $warehouse_id
     */
    public function __construct($http_client, string $token, string $warehouse_id)
    {
        parent::__construct($http_client, $token);

        $this->setWarehouseId($warehouse_id);
    }

    /**
     * @param string $endpoint
     * @return BizimHesapB2bInventory
     */
    public function setEndpoint(string $endpoint): BizimHesapB2bInventory
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @param string $warehouse_id
     * @return BizimHesapB2bInventory
     */
    public function setWarehouseId(string $warehouse_id): BizimHesapB2bInventory
    {
        $this->warehouse_id = $warehouse_id;

        return $this;
    }

    /**
     * @return Collection|Warehouse[]
     * @throws Exceptions\BizimHesapB2bException
     * @throws RequestException
     */
    public function get()
    {
        $this->endpoint = strtr($this->endpoint, ["{warehouse-id}" => $this->warehouse_id]);

        $response = $this->http_client::withHeaders([
            'token' => $this->token,
        ])->get($this->endpoint);

        $this->checkForErrors($response);

        if ($response->successful()) {

            $result = $this->collect($response->body());

            if ($result->has("inventory")) {

                $result->put("inventory", $result->get("inventory")->mapInto(InventoryItem::class));

                return $result->get("inventory")->toArray();

            }

        }

        return $result;
    }

    protected function collect(string $response): Collection
    {
        $collected = collect(json_decode($response, true));

        if (isset($collected->get("data")["inventory"])) {

            $collected->put("inventory", collect($collected->get("data")["inventory"]));

        }

        $collected->offsetUnset("data");

        return $collected;
    }
}
