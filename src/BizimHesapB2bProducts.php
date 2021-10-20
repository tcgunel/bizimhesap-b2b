<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use TCGunel\BizimHesapB2b\Models\Responses\Photo;
use TCGunel\BizimHesapB2b\Models\Responses\Product;

class BizimHesapB2bProducts extends BizimHesapB2b
{
    use HandleErrors;

    protected $endpoint = "https://bizimhesap.com/api/b2b/products";

    /**
     * BizimHesapB2bProducts constructor.
     * @param Http|null http_client
     * @param string $token
     */
    public function __construct($http_client, string $token)
    {
        parent::__construct($http_client, $token);
    }

    /**
     * @param string $endpoint
     * @return BizimHesapB2bProducts
     */
    public function setEndpoint(string $endpoint): BizimHesapB2bProducts
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return Collection|Product[]
     * @throws Exceptions\BizimHesapB2bException
     * @throws RequestException
     */
    public function get(): array
    {
        $response = $this->http_client::withHeaders([
            'token' => $this->token,
        ])->get($this->endpoint);

        $this->checkForErrors($response);
        
        $result = [];

        if ($response->successful()) {

            $result = $this->collect($response->body());

            if ($result->has("products")) {

                $result->put("products", $result->get("products")->mapInto(Product::class));

                return $result->get("products")->toArray();

            }

        }

        return $result;
    }

    protected function collect(string $response): Collection
    {
        $collected = collect(json_decode($response, true));

        if (isset($collected->get("data")["products"])) {

            $collected->put("products", collect($collected->get("data")["products"])->map(function ($product) {

                if (!empty($product["photo"])) {

                    $product["photo"] = collect(json_decode($product["photo"], true))->map(function ($photo) {

                        return new Photo(["is_cover" => !!$photo["FlCover"], "url" => $photo["PhotoUrl"]]);


                    })->toArray();

                } else {

                    $product["photo"] = [];

                }

                return $product;
            }));

        }

        $collected->offsetUnset("data");

        return $collected;
    }
}
