<?php

namespace TCGunel\BizimHesapB2b\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use TCGunel\BizimHesapB2b\BizimHesapB2bAbstracts;
use TCGunel\BizimHesapB2b\BizimHesapB2bAddInvoice;
use TCGunel\BizimHesapB2b\BizimHesapB2bCancelInvoice;
use TCGunel\BizimHesapB2b\BizimHesapB2bCustomers;
use TCGunel\BizimHesapB2b\BizimHesapB2bInventory;
use TCGunel\BizimHesapB2b\BizimHesapB2bProducts;
use TCGunel\BizimHesapB2b\BizimHesapB2bWarehouses;
use TCGunel\BizimHesapB2b\Constants\InvoiceType;
use TCGunel\BizimHesapB2b\Exceptions\BizimHesapB2bException;
use TCGunel\BizimHesapB2b\Models\Responses\_Abstract;
use TCGunel\BizimHesapB2b\Models\Responses\AbstractUser;
use TCGunel\BizimHesapB2b\Models\Responses\Customer;
use TCGunel\BizimHesapB2b\Models\Responses\InventoryItem;
use TCGunel\BizimHesapB2b\Models\Requests\Invoice;
use TCGunel\BizimHesapB2b\Models\Requests\InvoiceAmounts;
use TCGunel\BizimHesapB2b\Models\Requests\InvoiceCustomer;
use TCGunel\BizimHesapB2b\Models\Requests\InvoiceDates;
use TCGunel\BizimHesapB2b\Models\Requests\InvoiceProduct;
use TCGunel\BizimHesapB2b\Models\Responses\Photo;
use TCGunel\BizimHesapB2b\Models\Responses\Product;
use TCGunel\BizimHesapB2b\Models\Responses\Warehouse;
use TCGunel\BizimHesapB2b\Tests\TestCase;

class BizimHesapB2bTest extends TestCase
{
    public $api_error = '{
            "resultCode": 0,
            "errorText": "Error message from api.",
            "data": {}
        }';

    public $api_token_error = '{
            "Message": "Token is invalid."
        }';

    public $token = "A186C6A6B05C45C2A705BE01369A537E";

    public $customer_id = "66C3492F39E04BCC9FA6E012D61FD274";

    public $warehouse_id = "73EC526C6E734BE3A290A604C01BE8A7";

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function invoice_products(): array
    {
        return [
            [
                // Gross 20000.0 ( Unit * Quantity )
                // Net 16363.64 ( (Gross - Discount) / ( Tax / 100 + 1 ) )
                // Tax 1636.36 ( Gross - Discount - Net )
                // Total 18000.0 ( Net + Tax )
                "productId"   => 7,
                "productName" => "LG TV",
                "note"        => "",
                "barcode"     => 7777777,
                "taxRate"     => 10,
                "quantity"    => 2,
                "unitPrice"   => 10000,
                "discount"    => 2000,
            ],
            [
                // Gross 1000.0 ( Unit * Quantity )
                // Net 818.18 ( (Gross - Discount) / ( Tax / 100 + 1 ) )
                // Tax 81.82 ( Gross - Discount - Net )
                // Total 900.0 ( Net + Tax )
                "productId"   => 8,
                "productName" => "Kahve Makinası",
                "note"        => "",
                "barcode"     => 888888,
                "taxRate"     => 10,
                "quantity"    => 1,
                "unitPrice"   => 1000,
                "discount"    => 100,
            ],
        ];
    }

    protected function prepare_invoice(): Invoice
    {
        $invoice = new Invoice();

        $invoice->firmId      = $this->token;
        $invoice->invoiceNo   = $this->faker->randomNumber(7);
        $invoice->invoiceType = $this->faker->randomElement([InvoiceType::SALE, InvoiceType::PURCHASE]);
        $invoice->note        = $this->faker->realText(254);

        $dates = new InvoiceDates();

        $dates->setInvoiceDate(Carbon::now());
        $dates->setDueDate(Carbon::now());
        $dates->setDeliveryDate(Carbon::now());

        $invoice->dates = $dates;

        $customer = new InvoiceCustomer();

        $customer->customerId = $this->faker->randomNumber(5);
        $customer->title      = $this->faker->company;
        $customer->taxOffice  = $this->faker->realText(10);
        $customer->taxNo      = $this->faker->randomNumber(5);
        $customer->email      = $this->faker->companyEmail;
        $customer->address    = $this->faker->realText(50);
        $customer->setPhone($this->faker->phoneNumber);

        $invoice->customer = $customer;

        foreach ($this->invoice_products() as $invoice_product) {

            $product = new InvoiceProduct();

            foreach ($invoice_product as $key => $value) {

                $product->$key = $value;

            }

            $invoice->details[] = $product;

        }

        $amounts = new InvoiceAmounts();

        $amounts->setCurrency("TRY");

        $invoice->amounts = $amounts;

        return $invoice;
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
     *
     * @return void
     * @throws BizimHesapB2bException
     * @throws RequestException
     */
    public function test_can_add_invoice()
    {
        $response = '{
            "url": "https://bizimhesap.com/web/ngn/doc/printout?rc=1&id=D745B7B91F924C138A8104FF097E74E6&tp=9D0DF2985D6043C4903BB3FDB2B78E14",
            "guid": "D745B7B91F924C138A8104FF097E74E6",
            "error": "",
            "eInvoiceNo": ""
        }';

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $invoice = $this->prepare_invoice();

        $add_invoice = new BizimHesapB2bAddInvoice($http_client, $this->token, $invoice);

        $add_invoice->run();

        $this->assertEquals(20000.0, $invoice->details[0]->getGrossPrice());
        $this->assertEquals(16363.64, $invoice->details[0]->getNet());
        $this->assertEquals(1636.36, $invoice->details[0]->getTax());
        $this->assertEquals(18000.0, $invoice->details[0]->getTotal());
        $this->assertEquals(2000, $invoice->details[0]->discount);

        $this->assertEquals(1000.0, $invoice->details[1]->getGrossPrice());
        $this->assertEquals(818.18, $invoice->details[1]->getNet());
        $this->assertEquals(81.82, $invoice->details[1]->getTax());
        $this->assertEquals(900.0, $invoice->details[1]->getTotal());
        $this->assertEquals(100, $invoice->details[1]->discount);

        $this->assertEquals(21000.0, $invoice->amounts->getGross());
        $this->assertEquals(2100, $invoice->amounts->getDiscount());
        $this->assertEquals(1718.18, $invoice->amounts->getTax());
        $this->assertEquals(17181.82, $invoice->amounts->getNet());
        $this->assertEquals(18900.0, $invoice->amounts->getTotal());
    }

    public function test_can_add_invoice_throws_api_error_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = '{
            "url": "https://bizimhesap.com/web/ngn/doc/printout?rc=1&id=D745B7B91F924C138A8104FF097E74E6&tp=9D0DF2985D6043C4903BB3FDB2B78E14",
            "guid": "D745B7B91F924C138A8104FF097E74E6",
            "error": "Error message.",
            "eInvoiceNo": ""
        }';

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $invoice = $this->prepare_invoice();

        $add_invoice = new BizimHesapB2bAddInvoice($http_client, $this->token, $invoice);

        $add_invoice->run();
    }

    public function test_can_add_invoice_throws_token_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_token_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response, 401);

        });

        $invoice = $this->prepare_invoice();

        $add_invoice = new BizimHesapB2bAddInvoice($http_client, $this->token, $invoice);

        $add_invoice->run();
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
     * @throws BizimHesapB2bException
     */
    public function test_can_cancel_invoice()
    {
        $response = '{
            "error": "",
            "status": "0"
        }';

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $cancelInvoice = new BizimHesapB2bCancelInvoice($http_client, $this->token, "guid");

        $result = $cancelInvoice->run();

        $this->assertTrue($result);
    }

    public function test_can_cancel_invoice_throws_api_error_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = '{
            "error": "Error message.",
            "status": "1"
        }';

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $cancelInvoice = new BizimHesapB2bCancelInvoice($http_client, $this->token, "guid");

        $cancelInvoice->run();
    }

    public function test_can_cancel_invoice_throws_token_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_token_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response, 401);

        });

        $cancelInvoice = new BizimHesapB2bCancelInvoice($http_client, $this->token, "guid");

        $cancelInvoice->run();
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
        $response = '{
                        "resultCode": 1,
                        "errorText": "",
                        "data": {
                            "products": [
                                {
                                    "id": "BE51CAB573B94A88AA6BD1FB2E717B22",
                                    "code": "",
                                    "barcode": "",
                                    "title": "Proje danışmanlık",
                                    "price": 15.99000,
                                    "currency": "TL",
                                    "unit": "Adet",
                                    "tax": 18.0,
                                    "photo": "",
                                    "description": "",
                                    "ecommerceDescription": "",
                                    "variant": "",
                                    "quantity": 0.0,
                                    "brand": "",
                                    "category": "Hizmet",
                                    "photo": "[{\"PhotoUrl\":\"https://marketplace-single-product-images.oss-eu-central-1.aliyuncs.com/prod/124314/9dd74850-4830-4fff-966a-e2d07cc43aab/image00051.jpeg\",\"PhotoUrlOriginal\":\"https://marketplace-single-product-images.oss-eu-central-1.aliyuncs.com/prod/124314/9dd74850-4830-4fff-966a-e2d07cc43aab/image00051.jpeg\",\"FlCover\":true},{\"PhotoUrl\":\"https://marketplace-single-product-images.oss-eu-central-1.aliyuncs.com/prod/124314/465ddde3-1c77-4a65-a206-ead547d83cf7/image00078.jpeg\",\"PhotoUrlOriginal\":\"https://marketplace-single-product-images.oss-eu-central-1.aliyuncs.com/prod/124314/465ddde3-1c77-4a65-a206-ead547d83cf7/image00078.jpeg\",\"FlCover\":false}]"
                                },
                                {
                                    "id": "8F90D14A75EB40C8AF6D8544466D2BBE",
                                    "code": "TV001",
                                    "barcode": "8690123456789",
                                    "title": "LED Televizyon",
                                    "price": 1298.00,
                                    "currency": "TL",
                                    "unit": "Adet",
                                    "tax": 18.0,
                                    "photo": "",
                                    "description": "",
                                    "ecommerceDescription": "",
                                    "variant": "",
                                    "quantity": 12.000,
                                    "brand": "Örnek",
                                    "category": "Ev Elektroniği"
                                }
                            ]
                        }
                    }';

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $products = new BizimHesapB2bProducts($http_client, $this->token);

        $list = $products->get();

        $this->assertNotEmpty($list);
        $this->assertContainsOnlyInstancesOf(Product::class, $list);

        $this->assertNotEmpty($list[0]->photo);
        $this->assertContainsOnlyInstancesOf(Photo::class, $list[0]->photo);

        $this->assertEmpty($list[1]->photo);
    }

    public function test_can_get_products_throws_api_error_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $products = new BizimHesapB2bProducts($http_client, $this->token);

        $products->get();
    }

    public function test_can_get_products_throws_token_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_token_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response, 401);

        });

        $products = new BizimHesapB2bProducts($http_client, $this->token);

        $products->get();
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
        $response = '{
                        "resultCode": 1,
                        "errorText": "",
                        "data": {
                            "warehouses": [
                                {
                                    "id": "73EC526C6E734BE3A290A604C01BE8A7",
                                    "title": "Ana Depo"
                                }
                            ]
                        }
                    }';

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $warehouses = new BizimHesapB2bWarehouses($http_client, $this->token);

        $list = $warehouses->get();

        $this->assertNotEmpty($list);
        $this->assertContainsOnlyInstancesOf(Warehouse::class, $list);
    }

    public function test_can_get_warehouses_throws_api_error_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $customers = new BizimHesapB2bWarehouses($http_client, $this->token);

        $customers->get();
    }

    public function test_can_get_warehouses_throws_token_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_token_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response, 401);

        });

        $customers = new BizimHesapB2bWarehouses($http_client, $this->token);

        $customers->get();
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
     *
     * @throws BizimHesapB2bException
     * @throws RequestException
     */
    public function test_can_get_inventory()
    {
        $response = '{
            "resultCode": 1,
            "errorText": "",
            "data": {
                "inventory": [
                    {
                        "id": "8F90D14A75EB40C8AF6D8544466D2BBE",
                        "title": "LED Televizyon",
                        "qty": "12"
                    }
                ]
            }
        }';

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $inventory = new BizimHesapB2bInventory($http_client, $this->token, $this->warehouse_id);

        $list = $inventory->get();

        $this->assertNotEmpty($list);
        $this->assertContainsOnlyInstancesOf(InventoryItem::class, $list);
    }

    public function test_can_get_inventory_throws_api_error_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $inventory = new BizimHesapB2bInventory($http_client, $this->token, $this->warehouse_id);

        $inventory->get();
    }

    public function test_can_get_inventory_throws_token_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_token_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response, 401);

        });

        $inventory = new BizimHesapB2bInventory($http_client, $this->token, $this->warehouse_id);

        $inventory->get();
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
        $response = '{
            "resultCode": 1,
            "errorText": "",
            "data": {
                "customers": [
                    {
                        "id": "66C3492F39E04BCC9FA6E012D61FD274",
                        "code": "7777777777",
                        "title": "Test Müşteri",
                        "address": "Bağ sokak, MRC Life Apartmanı, No 23, Daire 2",
                        "phone": "5554443322",
                        "taxno": "12345678901",
                        "taxoffice": "Nilüfer V.D.",
                        "authorized": "Ahmet Mehmet",
                        "balance": "100.000,00",
                        "email": "test@msn.com"
                    }
                ]
            }
        }';

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $customers = new BizimHesapB2bCustomers($http_client, $this->token);

        $list = $customers->get();

        $this->assertNotEmpty($list);
        $this->assertContainsOnlyInstancesOf(Customer::class, $list);
    }

    public function test_can_get_customers_throws_api_error_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $customers = new BizimHesapB2bCustomers($http_client, $this->token);

        $customers->get();
    }

    public function test_can_get_customers_throws_token_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_token_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response, 401);

        });

        $customers = new BizimHesapB2bCustomers($http_client, $this->token);

        $customers->get();
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
     *
     * @throws BizimHesapB2bException
     * @throws RequestException
     */
    public function test_can_get_abstract()
    {
        $response = '{
                        "resultCode": 1,
                        "errorText": "",
                        "data": {
                            "title": "Test Müşterii",
                            "email": "test@msn.com",
                            "phone": "5554443322",
                            "balance": "36.000,00 TL",
                            "debitSum": "101000,00",
                            "creditSum": "65000,00",
                            "link": "https://bzmhsp.page.link/H6qxQ",
                            "abstract": [
                                {
                                    "trxdate": "01.01.2021",
                                    "type": "Hesap Açılışı",
                                    "note": "Açılış bakiyesi",
                                    "payment": "",
                                    "debit": "100.000,00",
                                    "credit": "",
                                    "balance": "100000,00"
                                },
                                {
                                    "trxdate": "05.04.2021",
                                    "type": "Tahsilat",
                                    "note": "tahsilat açıklaması",
                                    "payment": "Nakit",
                                    "debit": "",
                                    "credit": "50.000,00",
                                    "balance": "50000,00"
                                },
                                {
                                    "trxdate": "05.04.2021",
                                    "type": "Tahsilat",
                                    "note": "senet",
                                    "payment": "Senet (05.05.2021)",
                                    "debit": "",
                                    "credit": "7.500,00",
                                    "balance": "42500,00"
                                },
                                {
                                    "trxdate": "05.04.2021",
                                    "type": "Tahsilat",
                                    "note": "senet",
                                    "payment": "Senet (05.06.2021)",
                                    "debit": "",
                                    "credit": "7.500,00",
                                    "balance": "35000,00"
                                },
                                {
                                    "trxdate": "05.04.2021",
                                    "type": "Ödeme",
                                    "note": "(Verilen kendi çekiniz) Akbank No:111111",
                                    "payment": "Çek (21.04.2021)",
                                    "debit": "1.000,00",
                                    "credit": "",
                                    "balance": "36000,00"
                                }
                            ]
                        }
                    }';

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $abstracts = new BizimHesapB2bAbstracts($http_client, $this->token, $this->customer_id);

        $abstracts_instance = $abstracts->get();

        $this->assertNotEmpty($abstracts_instance);
        $this->assertInstanceOf(AbstractUser::class, $abstracts_instance);
        $this->assertContainsOnlyInstancesOf(_Abstract::class, $abstracts_instance->abstract);
    }

    public function test_can_get_abstract_throws_api_error_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response);

        });

        $abstracts_instance = new BizimHesapB2bAbstracts($http_client, $this->token, $this->customer_id);

        $abstracts_instance->get();
    }

    public function test_can_get_abstract_throws_token_exception()
    {
        $this->expectException(BizimHesapB2bException::class);

        $response = $this->api_token_error;

        $http_client = Http::fake(function ($request) use ($response) {

            return Http::response($response, 401);

        });

        $abstracts_instance = new BizimHesapB2bAbstracts($http_client, $this->token, $this->customer_id);

        $abstracts_instance->get();
    }
}
