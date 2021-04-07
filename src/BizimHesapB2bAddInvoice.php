<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Support\Facades\Http;
use TCGunel\BizimHesapB2b\Models\Requests\Invoice;
use TCGunel\BizimHesapB2b\Models\Responses\Invoice as InvoiceResponse;

class BizimHesapB2bAddInvoice extends BizimHesapB2b
{
    use HandleErrors;

    protected $endpoint = "https://bizimhesap.com/api/b2b/addinvoice";

    protected $invoice;

    /**
     * BizimHesapB2bAddInvoice constructor.
     * @param Http|null http_client
     * @param string $token
     * @param Invoice $invoice
     */
    public function __construct($http_client, string $token, Invoice $invoice)
    {
        parent::__construct($http_client, $token);

        $this->invoice = $invoice;
    }

    /**
     * @param string $endpoint
     * @return BizimHesapB2bAddInvoice
     */
    public function setEndpoint(string $endpoint): BizimHesapB2bAddInvoice
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @return InvoiceResponse
     * @throws Exceptions\BizimHesapB2bException
     */
    public function run()
    {
        foreach ($this->invoice->details as $invoiceProduct) {

            $invoiceProduct->calculateValues();

        }

        $this->invoice->amounts->calculateAmounts($this->invoice->details);

        $response = $this->http_client::withHeaders([
            'token' => $this->token,
        ])->post($this->endpoint, $this->invoice->jsonReadyArray());

        $this->checkForErrors($response);

        if ($response->successful()) {

            return new InvoiceResponse(json_decode($response->body(), true));

        }
    }

}
