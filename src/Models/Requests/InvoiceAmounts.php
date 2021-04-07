<?php

namespace TCGunel\BizimHesapB2b\Models\Requests;

class InvoiceAmounts
{
    /** @var string */
    protected $currency = "TL";

    /** @var float */
    protected $gross;

    /** @var float */
    protected $discount;

    /** @var float */
    protected $net;

    /** @var float */
    protected $tax;

    /** @var float */
    protected $total;

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        if ($currency === "TRY") {

            $currency = "TL";

        }

        $this->currency = $currency;
    }

    /**
     * @param InvoiceProduct[] $invoice_products
     */
    public function calculateAmounts(array $invoice_products)
    {
        foreach ($invoice_products as $invoice_product) {

            $this->gross += $invoice_product->getGrossPrice();

            $this->discount += $invoice_product->discount;

            $this->net += $invoice_product->getNet();

            $this->tax += $invoice_product->getTax();

            $this->total += $invoice_product->getTotal();

        }
    }

    /**
     * @return float
     */
    public function getGross(): float
    {
        return $this->gross;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @return float
     */
    public function getNet(): float
    {
        return $this->net;
    }

    /**
     * @return float
     */
    public function getTax(): float
    {
        return $this->tax;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    public function __toArray()
    {
        return call_user_func('get_object_vars', $this);
    }
}
