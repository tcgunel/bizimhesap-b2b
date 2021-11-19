<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use TCGunel\BizimHesapB2b\Models\Responses\Photo;
use TCGunel\BizimHesapB2b\Models\Responses\Product;
use TCGunel\BizimHesapB2b\Models\Responses\ProductVariant;
use TCGunel\BizimHesapB2b\Models\Responses\ProductVariantGroup;

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

        return !is_array($result) ? $result->toArray() : $result;
    }

    protected function collect(string $response): Collection
    {
        $collected = collect(json_decode($response, true));

        if (isset($collected->get("data")["products"])) {

			$modifiedProducts = collect($collected->get("data")["products"])->reduce(function ($carry, $product) {

				if (!empty($product["photo"])) {

					$product["photo"] = collect(json_decode($product["photo"], true))->map(function ($photo) {

						return new Photo(["is_cover" => (boolean)$photo["FlCover"], "url" => $photo["PhotoUrl"]]);


					})->toArray();

				} else {

					$product["photo"] = [];

				}

				$variantName = data_get($product, "variantName");

				if (!empty($variantName)){

					$variant = new ProductVariant([
						"name"    => $product["variant"],
						"price"   => !empty(data_get($product, "variantPrice")) ? $product["variantPrice"] : $product["price"],
						"code"    => $product["code"],
						"barcode" => $product["barcode"],
					]);

					if ($found = collect($carry)->where("id", $product["id"])->keys()->first()){

						$carry[$found]["variantGroup"]->variants[] = $variant;

						return $carry;

					}

					$product["variantGroup"] = new ProductVariantGroup([
						"name"     => $variantName,
						"variants" => [$variant],
					]);

				}

				$carry[] = $product;

				return $carry;
			}, []);

            $collected->put("products", collect($modifiedProducts));

        }

        $collected->offsetUnset("data");

        return $collected;
    }
}
