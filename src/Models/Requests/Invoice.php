<?php

namespace TCGunel\BizimHesapB2b\Models\Requests;

class Invoice
{
    /** @var string */
    public $firmId;

    /** @var string */
    public $invoiceNo;

    /** @var integer */
    public $invoiceType;

    /** @var string */
    public $note;

    /** @var InvoiceDates */
    public $dates;

    /** @var InvoiceCustomer */
    public $customer;

    /** @var InvoiceAmounts */
    public $amounts;

    /** @var InvoiceProduct[] */
    public $details = [];

    public function jsonReadyArray(): array
    {
        $json_ready_array = [];

        $properties = get_object_vars($this);

        foreach ($properties as $property => $value) {

            if ($value instanceof InvoiceDates || $value instanceof InvoiceCustomer || $value instanceof InvoiceAmounts) {

                $json_ready_array[$property] = $value->__toArray();

            } else if (is_array($value)) {

                $json_ready_array[$property] = [];

                foreach ($value as $item){

                    if ($item instanceof InvoiceProduct){

                        $json_ready_array[$property][] = $item->__toArray();

                    }

                }

            } else {

                $json_ready_array[$property] = $value;
            }

        }

        return $json_ready_array;
    }
}
