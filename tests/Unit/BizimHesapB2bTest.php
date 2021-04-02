<?php

namespace TCGunel\BizimHesapB2b\Tests\Unit;

use TCGunel\BizimHesapB2b\Tests\TestCase;

class BizimHesapB2bTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

    }

    /**
     * AddInvoice
     *
     * @method POST
     * @url https://bizimhesap.com/api/b2b/addinvoice
     *
     * @description Alış veya satış faturası ekleyen metottur.
     *              Gönderilen datadaki müşteri ve ürün bilgileri bizimhesap üzerinde mevcut değilse otomatik olarak
     *              oluşturulurlar ve ardından fatura kaydedilir.
     *
     * @param string {FirmId} Bizimhesap tarafından verilecek özel tekil ID.
     * @param string {InvoiceNo} Faturanın belge numarası (isteğe bağlı).
     * @param string {InvoiceType} Fatura tipi (3:Satış, 5:Alış).
     * @param string {Note} Fatura açıklaması (isteğe bağlı).
     * @param string {Dates}
     * @param string {Customer}
     * @param string {Details[]}
     * @param string {Amounts}
     *
     * @returns string {error} Dolu ise hata mesajıdır.
     * @returns string {guid} Faturaya bizimhesap tarafından verilen ID.
     * @returns string {url} Faturanın PDF görüntüsünü içeren link.
     */
    public function test_can_add_invoice()
    {

    }

    public function test_can_add_invoice_throws_api_error_exception()
    {

    }

    public function test_can_add_invoice_throws_required_field_exception()
    {

    }

    /**
     * CancelInvoice
     *
     * @method POST
     * @url https://bizimhesap.com/api/b2b/cancelinvoice
     *
     * @description Alış veya satış faturası iptal eden metottur.
     *              AddInvoice metodundan alınan guid değeri ile fatura iptali sağlanır.
     *
     * @param string {FirmId} Bizimhesap tarafından verilecek özel tekil ID.
     * @param string {Guid} AddInvoice metodundan alınmış olan guid değeri.
     *
     * @returns string {error} Dolu ise hata mesajıdır.
     * @returns string {status} Hata yoksa 0, hata varsa 1 döner.
     */
    public function test_can_cancel_invoice()
    {

    }

    public function test_can_cancel_invoice_throws_api_error_exception()
    {

    }

    public function test_can_cancel_invoice_throws_required_field_exception()
    {

    }

    /**
     * Products
     *
     * @method GET
     * @url https://bizimhesap.com/api/b2b/products
     * @header {token} hesabınıza ait token.
     *
     * @description Kayıtlı ürünlerin listesini getiren servistir.
     */
    public function test_can_get_products()
    {

    }

    public function test_can_get_products_throws_header_missing_exception()
    {

    }

    /**
     * Warehouses
     *
     * @method GET
     * @url https://bizimhesap.com/api/b2b/warehouses
     * @header {token} hesabınıza ait token
     *
     * @description Kayıtlı depoların listesini getiren servistir.
     */
    public function test_can_get_warehouses()
    {

    }

    public function test_can_get_warehouses_throws_header_missing_exception()
    {

    }

    /**
     * Inventory
     *
     * @method GET
     * @url https://bizimhesap.com/api/b2b/inventory/{depo-id}
     * @header {token} hesabınıza ait token.
     *
     * @description Bir deponun stoklarını getiren servistir.
     *
     * @param {depo-id} warehouses servisinden alınan depo kodu.
     */
    public function test_can_get_inventory()
    {

    }

    public function test_can_get_inventory_throws_header_missing_exception()
    {

    }

    public function test_can_get_inventory_throws_required_field_exception()
    {

    }

    /**
     * Customers
     *
     * @method GET
     * @url https://bizimhesap.com/api/b2b/customers
     * @header {token} hesabınıza ait token.
     *
     * @description Kayıtlı müşterilerin listesini getiren servistir.
     */
    public function test_can_get_customers()
    {

    }

    public function test_can_get_customers_throws_header_missing_exception()
    {

    }

    /**
     * Abstract
     *
     * @method GET
     * @url https://bizimhesap.com/api/b2b/abstract/{musteri-id}
     * @header {token} hesabınıza ait token.
     *
     * @description Bir carinin hesap ekstresini getiren servistir.
     *
     * @param {musteri-id} İlgili müşterinin bizimhesap müşteri kartındaki cari kodu.
     *                     (AddInvoice servisindeki CustomerID ile aynı değer).
     */
    public function test_can_get_abstract()
    {

    }

    public function test_can_get_abstract_throws_header_missing_exception()
    {

    }

    public function test_can_get_abstract_throws_required_field_exception()
    {

    }
}
