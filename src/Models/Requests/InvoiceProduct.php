<?php

namespace TCGunel\BizimHesapB2b\Models\Requests;

class InvoiceProduct
{
    /** @var integer */
    public $productId;

    /** @var string */
    public $productName;

    /** @var string */
    public $note;

    /** @var string */
    public $barcode;

    /** @var float */
    public $taxRate;

    /** @var integer */
    public $quantity;

    /**
     * Vergi dahil fiyat.
     *
     * @var float
     */
    public $unitPrice;

    /**
     * Vergi dahil indirimsiz fiyat X miktar
     *
     * @var float
     */
    protected $grossPrice;

    /**
     * İndirim tutarı
     *
     * @var float
     */
    public $discount = 0;

    /**
     * İndirim sonrası, vergisiz tutar
     *
     * @var float
     */
    protected $net;

    /** @var float */
    protected $tax;

    /**
     * Net + vergi
     *
     * @var float
     */
    protected $total;

    public function calculateValues()
    {
        $this->grossPrice = $this->round($this->unitPrice * $this->quantity);

        $this->net = $this->round($this->grossPrice - ($this->grossPrice / (1 + ($this->taxRate / 100)) * ($this->taxRate / 100)));

        $this->tax = $this->round($this->grossPrice - $this->net);

        $this->discount += $this->tax;

        $this->total = $this->round($this->net + $this->tax);
    }

    private function round($price): float
    {
        return round($price, 2);
    }

    /**
     * @return float
     */
    public function getGrossPrice(): float
    {
        return $this->grossPrice;
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
