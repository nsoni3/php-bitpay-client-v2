<?php

namespace BitPaySDK\Test;


use BitPaySDK;
use BitPaySDK\Model\Bill\BillStatus;
use BitPaySDK\Model\Currency;
use BitPaySDK\Model\Invoice\Invoice as Invoice;
use BitPaySDK\Model\Payout\PayoutStatus;
use BitPaySDK\Model\Payout\RecipientStatus;
use BitPaySDK\Model\Payout\RecipientReferenceMethod;
use PHPUnit\Framework\TestCase;

class BitPayTest extends TestCase
{
    /**
     * @var BitPaySDK\Client
     */
    protected $client;
    protected $client1;
    protected $client2;
    protected $clientMock;

    protected function setUp(): void
    {
        /**
         * You need to generate new tokens first
         * */
        $this->clientMock = $this->createMock(BitPaySDK\Client::class);
        $this->clientMock->withData(
            BitPaySDK\Env::Test,
            __DIR__."/../../examples/bitpay_private_test.key",
            new BitPaySDK\Tokens(
                "7UeQtMcsHamehE4gDZojUQbNRbSuSdggbH17sawtobGJ",
                "5j48K7pUrX5k59DLhRVYkCupgw2CtoEt8DBFrHo2vW47"
            ),
            "YourMasterPassword"
        );

        // $this->client = BitPaySDK\Client::create()->withData(
        //     BitPaySDK\Env::Test,
        //     __DIR__."/../../examples/bitpay_private_test.key",
        //     new BitPaySDK\Tokens(
        //         "7UeQtMcsHamehE4gDZojUQbNRbSuSdggbH17sawtobGJ",
        //         "5j48K7pUrX5k59DLhRVYkCupgw2CtoEt8DBFrHo2vW47"
        //     ),
        //     "YourMasterPassword");

        /**
         * Uncomment only if you wish to test the client with config files
         * */
        $this->client = BitPaySDK\Client::create()->withFile(__DIR__."/../../examples/BitPay.config.json");
//        $this->client2 = BitPaySDK\Client::create()->withFile(__DIR__."/../../examples/BitPay.config.yml");


        $this->assertNotNull($this->client);
        /**
         * Uncomment only if you wish to test the client with config files
         * */
//        $this->assertNotNull($this->client1);
//        $this->assertNotNull($this->client2);
    }

    public function testShouldGetInvoiceId()
    {
        $invoice = new Invoice(2.16, "eur");
        $invoice->setOrderId("98e572ea-910e-415d-b6de-65f5090680f6");
        $invoice->setFullNotifications(true);
        $invoice->setExtendedNotifications(true);
        $invoice->setTransactionSpeed("medium");
        $invoice->setNotificationURL("https://hookbin.com/lJnJg9WW7MtG9GZlPVdj");
        $invoice->setRedirectURL("https://hookbin.com/lJnJg9WW7MtG9GZlPVdj");
        $invoice->setPosData("98e572ea35hj356xft8y8cgh56h5090680f6");
        $invoice->setItemDesc("Ab tempora sed ut.");

        $buyer = new BitPaySDK\Model\Invoice\Buyer();
        $buyer->setName("Bily Matthews");
        $buyer->setEmail("sandbox@bitpay.com");
        $buyer->setAddress1("168 General Grove");
        $buyer->setAddress2("sandbox@bitpay.com");
        $buyer->setCountry("AD");
        $buyer->setLocality("Port Horizon");
        $buyer->setNotify(true);
        $buyer->setPhone("+99477512690");
        $buyer->setPostalCode("KY7 1TH");
        $buyer->setRegion("New Port");

        $invoice->setBuyer($buyer);

        try {
            $basicInvoice = $this->client->createInvoice($invoice);
            $retrievedInvoice = $this->client->getInvoice($basicInvoice->getId());//JHJsfknvgUpZjL9ksSKFZu
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicInvoice->getId());
        $this->assertNotNull($retrievedInvoice->getId());
        $this->assertEquals($basicInvoice->getId(), $retrievedInvoice->getId());
    }

    public function testShouldCreateInvoiceBtc()
    {
        try {
            $basicInvoice = $this->client->createInvoice(new Invoice(0.1, Currency::BTC));
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicInvoice->getId());

    }

    public function testShouldCreateInvoiceBch()
    {
        try {
            $basicInvoice = $this->client->createInvoice(new Invoice(0.1, Currency::BCH));
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicInvoice->getId());

    }

    public function testShouldCreateInvoiceEth()
    {
        try {
            $basicInvoice = $this->client->createInvoice(new Invoice(0.1, Currency::ETH));
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicInvoice->getId());

    }

    public function testShouldGetInvoicesWithDate()
    {
        $invoices = null;
        try {
            //check within the last few days
            $date = new \DateTime();
            $today = $date->format("Y-m-d");
            $dateBefore = $date->modify('-7 day');
            $sevenDaysAgo = $dateBefore->format("Y-m-d");
            $invoices = $this->client->getInvoices($sevenDaysAgo, $today, null, null, 46);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($invoices);
        $this->assertGreaterThan(0, count($invoices));
    }

    public function testShouldGetInvoicesWithDateTime()
    {
        $invoices = null;
        try {
            $dateTime = \DateTime::createFromFormat('Y-m-d H:i', "2022-04-06 10:00");
            $today = $dateTime->format("Y-m-d\TH:i:s.v\Z");
            $dateBefore = $dateTime->modify('-7 day');
            $sevenDaysAgo = $dateBefore->format("Y-m-d\TH:i:s.v\Z");
            $invoices = $this->client->getInvoices($sevenDaysAgo, $today, null, null, 46);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($invoices);
        $this->assertGreaterThan(0, count($invoices));
    }

    public function testShouldRequestInvoiceWebhook()
    {
        $basicInvoice = null;
        
        try {
            $basicInvoice = $this->client->createInvoice(new Invoice(0.1, Currency::BTC));
            $notificationStatus = $this->client->requestInvoiceNotification($basicInvoice->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicInvoice);
        $this->assertTrue($notificationStatus);
    }
            

    public function testShouldCreateUpdateAndDeleteInvoice()
    {
        $basicInvoice = null;
        $retreivedInvoice = null;
        $updatedInvoice = null;
        $cancelledInvoice = null;
        $retreivedCancelledInvoice = null;
        
        try {
            $basicInvoice = $this->client->createInvoice(new Invoice(0.1, Currency::BTC));
            $retreivedInvoice = $this->client->getInvoice($basicInvoice->getId());
            $updatedInvoice = $this->client->updateInvoice($retreivedInvoice->getId(), "sandbox@bitpay.com");
            $cancelledInvoice = $this->client->cancelInvoice($updatedInvoice->getId(), false);
            $retreivedCancelledInvoice = $this->client->getInvoice($cancelledInvoice->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicInvoice);
        $this->assertNotNull($retreivedInvoice);
        $this->assertNotNull($updatedInvoice);
        $this->assertNotNull($cancelledInvoice);
        $this->assertNotNull($retreivedCancelledInvoice);
    }

    public function testShouldPayInvoiceByStatus()
    {
        $basicInvoice = null;
        $payInvoice = null;        

        try {
            $basicInvoice = $this->client->createInvoice(new Invoice(0.1, Currency::BTC));
            $payInvoice = $this->client->payInvoice($basicInvoice->getId(), "confirmed");
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicInvoice);
        $this->assertNotNull($payInvoice);
    }

    public function testShouldCreateGetCancelRefundRequest()
    {
        $invoices = null;
        $firstInvoice = null;
        $lastRefund = null;
        $retrievedRefund = null;
        $retrievedRefunds = null;
        $cancelRefund = null;
        try {
            $date = new \DateTime();
            $today = $date->format("Y-m-d");
            $dateBefore = $date->modify('-90 day');
            $sevenDaysAgo = $dateBefore->format("Y-m-d");
            $invoices = $this->client->getInvoices(
                $sevenDaysAgo, $today, BitPaySDK\Model\Invoice\InvoiceStatus::Complete);
            $firstInvoice = $invoices[2];    
            $refunded = $this->client->createRefund(
                $firstInvoice->getId(), 1.0, "USD", true, false, false
            );
            $retrievedRefunds = $this->client->getRefunds($firstInvoice->getId());
            $lastRefund = end($retrievedRefunds);
            $updateRefund = $this->client->updateRefund($lastRefund->getId(), "created");
            $retrievedRefund = $this->client->getRefund($lastRefund->getId());
            $notificationStatus = $this->client->sendRefundNotification($lastRefund->getId());
            $cancelRefund = $this->client->cancelRefund($lastRefund->getId());
            $supportedWallets = $this->client->getSupportedWallets();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($invoices);
        $this->assertNotNull($retrievedRefunds);
        $this->assertEquals($updateRefund->getStatus(), "created");
        $this->assertEquals($lastRefund->getId(), $retrievedRefund->getId());
        $this->assertTrue($notificationStatus);
        $this->assertEquals($cancelRefund->getStatus(), "canceled");
        $this->assertNotNull($supportedWallets);
    }

    public function testShouldCreateBillUSD()
    {
        $items = [];

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(30.0);
        $item->setQuantity(9);
        $item->setDescription("product-a");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(14.0);
        $item->setQuantity(16);
        $item->setDescription("product-b");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(3.90);
        $item->setQuantity(42);
        $item->setDescription("product-c");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(6.99);
        $item->setQuantity(12);
        $item->setDescription("product-d");
        array_push($items, $item);

        $bill = new BitPaySDK\Model\Bill\Bill("1001", Currency::USD, "sandbox@bitpay.com", $items);
        $basicBill = null;
        try {
            $basicBill = $this->client->createBill($bill);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicBill->getId());
        $this->assertNotNull($basicBill->getItems()[0]->getId());
    }

    public function testShouldCreateBillEUR()
    {
        $items = [];

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(30.0);
        $item->setQuantity(9);
        $item->setDescription("product-a");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(14.0);
        $item->setQuantity(16);
        $item->setDescription("product-b");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(3.90);
        $item->setQuantity(42);
        $item->setDescription("product-c");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(6.99);
        $item->setQuantity(12);
        $item->setDescription("product-d");
        array_push($items, $item);

        $bill = new BitPaySDK\Model\Bill\Bill("1002", Currency::EUR, "sandbox@bitpay.com", $items);
        $basicBill = null;
        try {
            $basicBill = $this->client->createBill($bill);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicBill->getId());
        $this->assertNotNull($basicBill->getUrl());
        $this->assertEquals(BillStatus::Draft, $basicBill->getStatus());
        $this->assertNotNull($basicBill->getItems()[0]->getId());
    }

    public function testShouldGetBill()
    {
        $items = [];

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(30.0);
        $item->setQuantity(9);
        $item->setDescription("product-a");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(14.0);
        $item->setQuantity(16);
        $item->setDescription("product-b");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(3.90);
        $item->setQuantity(42);
        $item->setDescription("product-c");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(6.99);
        $item->setQuantity(12);
        $item->setDescription("product-d");
        array_push($items, $item);

        $bill = new BitPaySDK\Model\Bill\Bill("1003", Currency::EUR, "sandbox@bitpay.com", $items);
        $basicBill = null;
        $retrievedBill = null;
        try {
            $basicBill = $this->client->createBill($bill);
            $retrievedBill = $this->client->getBill($basicBill->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertEquals($basicBill->getId(), $retrievedBill->getId());
        $this->assertEquals($basicBill->getItems(), $retrievedBill->getItems());
    }

    public function testShouldUpdateBill()
    {
        $items = [];

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(30.0);
        $item->setQuantity(9);
        $item->setDescription("product-a");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(14.0);
        $item->setQuantity(16);
        $item->setDescription("product-b");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(3.90);
        $item->setQuantity(42);
        $item->setDescription("product-c");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(6.99);
        $item->setQuantity(12);
        $item->setDescription("product-d");
        array_push($items, $item);

        $bill = new BitPaySDK\Model\Bill\Bill("1004", Currency::EUR, "sandbox@bitpay.com", $items);
        $basicBill = null;
        $retrievedBill = null;
        $updatedBill = null;
        try {
            $basicBill = $this->client->createBill($bill);
            $retrievedBill = $this->client->getBill($basicBill->getId());

            $this->assertEquals($basicBill->getId(), $retrievedBill->getId());
            $this->assertEquals($basicBill->getItems(), $retrievedBill->getItems());
            $this->assertCount(4, $retrievedBill->getItems());

            $items = $retrievedBill->getItems();

            $item = new BitPaySDK\Model\Bill\Item();
            $item->setPrice(60);
            $item->setQuantity(7);
            $item->setDescription("product-added");
            array_push($items, $item);

            $retrievedBill->setItems($items);
            $updatedBill = $this->client->updateBill($retrievedBill, $retrievedBill->getId());
            $items = $updatedBill->getItems();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertCount(5, $updatedBill->getItems());
        $this->assertEquals(end($items)->getDescription(), "product-added");
    }

    public function testShouldGetBills()
    {
        $bills = null;
        try {
            $bills = $this->client->getBills();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertGreaterThan(0, count($bills));
    }

    public function testShouldGetBillsByStatus()
    {
        $bills = null;
        try {
            $bills = $this->client->getBills(BillStatus::Draft);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertGreaterThan(0, count($bills));
    }

    public function testShouldDeliverBill()
    {
        $items = [];

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(30.0);
        $item->setQuantity(9);
        $item->setDescription("product-a");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(14.0);
        $item->setQuantity(16);
        $item->setDescription("product-b");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(3.90);
        $item->setQuantity(42);
        $item->setDescription("product-c");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Bill\Item();
        $item->setPrice(6.99);
        $item->setQuantity(12);
        $item->setDescription("product-d");
        array_push($items, $item);

        $bill = new BitPaySDK\Model\Bill\Bill("1005", Currency::EUR, "sandbox@bitpay.com", $items);
        $basicBill = null;
        $retrievedBill = null;
        $result = null;
        try {
            $basicBill = $this->client->createBill($bill);
            $result = $this->client->deliverBill($basicBill->getId(), $basicBill->getToken());
            $retrievedBill = $this->client->getBill($basicBill->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertEquals($basicBill->getId(), $retrievedBill->getId());
        $this->assertEquals($basicBill->getItems(), $retrievedBill->getItems());
        $this->assertEquals("Success", $result);
        $this->assertNotEquals($basicBill->getStatus(), $retrievedBill->getStatus());
        $this->assertEquals($retrievedBill->getStatus(), BillStatus::Sent);
    }

    public function testShouldGetExchangeRates()
    {
        $ratesList = null;
        try {
            $rates = $this->client->getRates();
            $ratesList = $rates->getRates();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($ratesList);
    }

    public function testShouldGetEURExchangeRate()
    {
        $rate = null;
        try {
            $rates = $this->client->getRates();
            $rate = $rates->getRate(Currency::EUR);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotEquals(0, $rate);
    }

    public function testShouldGetCNYExchangeRate()
    {
        $rate = null;
        try {
            $rates = $this->client->getRates();
            $rate = $rates->getRate(Currency::CNY);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotEquals(0, $rate);
    }

    public function testShouldUpdateExchangeRates()
    {
        $rates = null;
        $ratesList = null;
        try {
            $rates = $this->client->getRates();
            $rates->update();
            $ratesList = $rates->getRates();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($ratesList);
    }

    public function testShouldGetETHExchangeRates()
    {
        $ratesList = null;
        try {
            $rates = $this->client->getCurrencyRates(Currency::ETH);
            $ratesList = $rates->getRates();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($ratesList);
    }

    public function testShouldGetETHToUSDExchangeRate()
    {
        $rate = null;
        try {
            $rate = $this->client->getCurrencyPairRate(Currency::ETH, Currency::USD);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($rate);
    }

    public function testShouldGetLedgerBtc()
    {
        $ledger = null;
        try {
            //check within the last few days
            $date = new \DateTime();
            $today = $date->format("Y-m-d");
            $dateBefore = $date->modify('-30 day');
            $sevenDaysAgo = $dateBefore->format("Y-m-d");
            $ledger = $this->client->getLedger(Currency::BTC, $sevenDaysAgo, $today);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($ledger);
        $this->assertGreaterThan(0, count($ledger));
    }

    public function testShouldGetLedgerUsd()
    {
        $ledger = null;
        try {
            //check within the last few days
            $date = new \DateTime();
            $today = $date->format("Y-m-d");
            $dateBefore = $date->modify('-30 day');
            $sevenDaysAgo = $dateBefore->format("Y-m-d");
            $ledger = $this->client->getLedger(Currency::USD, $sevenDaysAgo, $today);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($ledger);
        $this->assertGreaterThan(0, count($ledger));
    }

    public function testShouldGetLedgers()
    {
        $ledgers = null;
        try {
            $ledgers = $this->client->getLedgers();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($ledgers);
        $this->assertGreaterThan(0, count($ledgers));
    }

    public function testShouldSubmitPayoutRecipients()
    {
        $recipientsList = [
            new BitPaySDK\Model\Payout\PayoutRecipient(
                "sandbox@bitpay.com",
                "recipient1",
                "https://hookb.in/QJOPBdMgRkukpp2WO60o"),
            new BitPaySDK\Model\Payout\PayoutRecipient(
                "sandbox@bitpay.com",
                "recipient2",
                "https://hookb.in/QJOPBdMgRkukpp2WO60o"),
            new BitPaySDK\Model\Payout\PayoutRecipient(
                "sandbox@bitpay.com",
                "recipient3",
                "https://hookb.in/QJOPBdMgRkukpp2WO60o"),
        ];

        $recipientsObj = new BitPaySDK\Model\Payout\PayoutRecipients($recipientsList);
        try {
            $recipients = $this->client->submitPayoutRecipients($recipientsObj);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($recipients);
        $this->assertCount(3, $recipients);
    }

    public function testShouldGetPayoutRecipientId()
    {
        $recipientsList = [
            new BitPaySDK\Model\Payout\PayoutRecipient(
                "sandbox@bitpay.com",
                "recipient1",
                "https://hookb.in/QJOPBdMgRkukpp2WO60o"),
        ];

        $recipientsObj = new BitPaySDK\Model\Payout\PayoutRecipients($recipientsList);
        try {
            $basicRecipient = $this->client->submitPayoutRecipients($recipientsObj);
            $basicRecipient = reset($basicRecipient);
            $retrievedRecipient = $this->client->getPayoutRecipient($basicRecipient->getId());//9EsKtXQ1nj41EQ1Dk7VxhE
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicRecipient);
        $this->assertNotNull($retrievedRecipient->getId());
        $this->assertEquals($basicRecipient->getId(), $retrievedRecipient->getId());
    }

    public function testShouldGetPayoutRecipients()
    {
        $recipients = null;
        $status = 'active';
        try {
            $recipients = $this->client->getPayoutRecipients($status, 2);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($recipients);
        $this->assertCount(2, $recipients);
    }

    public function testShouldSubmitGetAndDeletePayoutRecipient()
    {
        $recipientsList = [
            new BitPaySDK\Model\Payout\PayoutRecipient(
                "sandbox@bitpay.com",
                "recipient1",
                "https://hookb.in/QJOPBdMgRkukpp2WO60o"),
        ];

        $recipientsObj = new BitPaySDK\Model\Payout\PayoutRecipients($recipientsList);
        try {
            $basicRecipient = $this->client->submitPayoutRecipients($recipientsObj);
            $basicRecipient = reset($basicRecipient);
            $retrievedRecipient = $this->client->getPayoutRecipient($basicRecipient->getId());//9EsKtXQ1nj41EQ1Dk7VxhE
            $retrievedRecipient->setLabel("updatedLabel");
            $updatedRecipient = $this->client->updatePayoutRecipient($retrievedRecipient->getId(), $retrievedRecipient);
            $deletedRecipient = $this->client->deletePayoutRecipient($retrievedRecipient->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicRecipient);
        $this->assertNotNull($retrievedRecipient->getId());
        $this->assertEquals($basicRecipient->getId(), $retrievedRecipient->getId());
        $this->assertEquals($retrievedRecipient->getStatus(), RecipientStatus::INVITED);
        $this->assertTrue($deletedRecipient);
        $this->assertEquals($updatedRecipient->getLabel(), "updatedLabel");
    }

    public function testShouldRequestPayoutRecipientNotification()
    {
        $result = null;
        $recipientsList = [
            new BitPaySDK\Model\Payout\PayoutRecipient(
                "sandbox@bitpay.com",
                "recipient1",
                "https://hookb.in/QJOPBdMgRkukpp2WO60o"),
        ];

        $recipientsObj = new BitPaySDK\Model\Payout\PayoutRecipients($recipientsList);
        try {
            $basicRecipient = $this->client->submitPayoutRecipients($recipientsObj);
            $basicRecipient = reset($basicRecipient);
            $result = $this->client->requestPayoutRecipientNotification($basicRecipient->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertTrue($result);
    }

    public function testShouldSubmitPayout()
    {
        $recipients = $this->client->getPayoutRecipients(null, 1);

        $currency = Currency::USD;
        $ledgerCurrency = Currency::USD;

        $payout = new BitPaySDK\Model\Payout\Payout(5.0, $currency, $ledgerCurrency);
        $payout->setRecipientId($recipients[0]->getId());
        
        $cancelledPayout = null;
        $createPayout = null;

        try {
            $createPayout = $this->client->submitPayout($payout);
            $cancelledPayout = $this->client->cancelPayout($createPayout->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($createPayout->getId());
        $this->assertTrue($cancelledPayout);
    }

    public function testShouldGetPayouts()
    {
        try {
            $payouts = $this->client->getPayouts();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertGreaterThan(0, count($payouts));
    }

    public function testShouldGetPayoutsByStatus()
    {
        try {
            $payouts = $this->client->getPayouts(null, null, PayoutStatus::Cancelled);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertGreaterThan(0, count($payouts));
    }

    public function testShouldSubmitGetAndDeletePayout()
    {
        $recipients = $this->client->getPayoutRecipients(null, 1);

        $currency = Currency::USD;
        $ledgerCurrency = Currency::USD;

        $payout = new BitPaySDK\Model\Payout\Payout(5.0, $currency, $ledgerCurrency);
        $payout->setRecipientId($recipients[0]->getId());

        $payoutRetrieved = null;

        try {
            $payout = $this->client->submitPayout($payout);
            $payoutRetrieved = $this->client->getPayout($payout->getId());
            $payoutCancelled = $this->client->cancelPayout($payoutRetrieved->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($payout->getId());
        $this->assertNotNull($payoutRetrieved->getId());
        $this->assertTrue($payoutCancelled);
        $this->assertEquals($payout->getId(), $payoutRetrieved->getId());
        $this->assertEquals($payoutRetrieved->getStatus(), PayoutStatus::New);
    }

    public function testShouldRequestPayoutNotification()
    {
        $recipients = $this->client->getPayoutRecipients(null, 1);

        $currency = Currency::USD;
        $ledgerCurrency = Currency::USD;

        $payout = new BitPaySDK\Model\Payout\Payout(5.0, $currency, $ledgerCurrency);
        $payout->setRecipientId($recipients[0]->getId());
        $payout->setNotificationEmail('sandbox@bitpay.com');
        $payout->setNotificationURL('https://hookb.in/QJOPBdMgRkukpp2WO60o');
        
        $cancelledPayout = null;
        $createPayout = null;
        $notificationSent = false;

        try {
            $createPayout = $this->client->submitPayout($payout);
            $notificationSent = $this->client->requestPayoutNotification($createPayout->getId());
            $cancelledPayout = $this->client->cancelPayout($createPayout->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($createPayout->getId());
        $this->assertTrue($notificationSent);
        $this->assertTrue($cancelledPayout);
    }

    public function testShouldSubmitPayoutBatch()
    {
        $recipients = $this->client->getPayoutRecipients(null, 2);
        $currency = Currency::USD;
        $ledgerCurrency = Currency::USD;

        $instructions = [
            new BitPaySDK\Model\Payout\PayoutInstruction(5.0, RecipientReferenceMethod::EMAIL, $recipients[0]->getEmail()),
            new BitPaySDK\Model\Payout\PayoutInstruction(6.0, RecipientReferenceMethod::RECIPIENT_ID, $recipients[1]->getId()),
        ];

        $batch = new BitPaySDK\Model\Payout\PayoutBatch($currency, $instructions, $ledgerCurrency);

        $cancelledPayoutBatch = null;

        try {
            $batch = $this->client->submitPayoutBatch($batch);
            $cancelledPayoutBatch = $this->client->cancelPayoutBatch($batch->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($batch->getId());
        $this->assertCount(2, $batch->getInstructions());
        $this->assertTrue($cancelledPayoutBatch);
    }

    public function testShouldGetPayoutBatches()
    {
        try {
            $batches = $this->client->getPayoutBatches();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertGreaterThan(0, count($batches));
    }

    public function testShouldGetPayoutBatchesByStatus()
    {
        try {
            $batches = $this->client->getPayoutBatches(null, null, PayoutStatus::Cancelled);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertGreaterThan(0, count($batches));
    }

    public function testShouldSubmitGetAndDeletePayoutBatch()
    {
        $recipients = $this->client->getPayoutRecipients(null, 2);
        $currency = Currency::USD;
        $ledgerCurrency = Currency::USD;

        $instructions = [
            new BitPaySDK\Model\Payout\PayoutInstruction(5.0, RecipientReferenceMethod::EMAIL, $recipients[0]->getEmail()),
            new BitPaySDK\Model\Payout\PayoutInstruction(6.0, RecipientReferenceMethod::RECIPIENT_ID, $recipients[1]->getId()),
        ];

        $batch = new BitPaySDK\Model\Payout\PayoutBatch($currency, $instructions, $ledgerCurrency);
        $batchRetrieved = null;
        $batchCancelled = null;

        try {
            $batch = $this->client->submitPayoutBatch($batch);
            $batchRetrieved = $this->client->getPayoutBatch($batch->getId());
            $batchCancelled = $this->client->cancelPayoutBatch($batchRetrieved->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($batch->getId());
        $this->assertNotNull($batchRetrieved->getId());
        $this->assertTrue($batchCancelled);
        $this->assertCount(2, $batch->getInstructions());
        $this->assertEquals($batch->getId(), $batchRetrieved->getId());
        $this->assertEquals($batchRetrieved->getStatus(), PayoutStatus::New);
    }

    public function testShouldRequestPayoutBatchNotification()
    {
        $recipients = $this->client->getPayoutRecipients(null, 2);

        $currency = Currency::USD;
        $ledgerCurrency = Currency::USD;

        $instructions = [
            new BitPaySDK\Model\Payout\PayoutInstruction(5.0, RecipientReferenceMethod::EMAIL, $recipients[0]->getEmail()),
            new BitPaySDK\Model\Payout\PayoutInstruction(6.0, RecipientReferenceMethod::RECIPIENT_ID, $recipients[1]->getId()),
        ];

        $batch = new BitPaySDK\Model\Payout\PayoutBatch($currency, $instructions, $ledgerCurrency);
        $batch->setNotificationEmail('sandbox@bitpay.com');
        $batch->setNotificationURL('https://hookb.in/QJOPBdMgRkukpp2WO60o');

        $cancelledPayoutBatch = null;
        $notificationSent = false;

        try {
            $batch = $this->client->submitPayoutBatch($batch);
            $notificationSent = $this->client->requestPayoutBatchNotification($batch->getId());
            $cancelledPayoutBatch = $this->client->cancelPayoutBatch($batch->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($batch->getId());
        $this->assertCount(2, $batch->getInstructions());
        $this->assertTrue($notificationSent);
        $this->assertTrue($cancelledPayoutBatch);
    }

    public function testGetSettlements()
    {
        $settlements = null;
        $firstSettlement = null;
        $settlement = null;
        try {
            //check within the last few days
            $date = new \DateTime();
            $today = $date->format("Y-m-d");
            $dateBefore = $date->modify('-365 day');
            $oneMonthAgo = $dateBefore->format("Y-m-d");

            $settlements = $this->client->getSettlements(Currency::USD, $oneMonthAgo, $today, null, null, null);
            $firstSettlement = $settlements[0];
            $settlement = $this->client->getSettlement($firstSettlement->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($settlements);
        $this->assertGreaterThan(0, count($settlements));
        $this->assertNotNull($settlement->getId());
        $this->assertEquals($firstSettlement->getId(), $settlement->getId());
    }

    public function testGetSettlementReconciliationReport()
    {
        $settlements = null;
        $firstSettlement = null;
        $settlement = null;
        try {
            //check within the last few days
            $date = new \DateTime();
            $today = $date->format("Y-m-d");
            $dateBefore = $date->modify('-365 day');
            $oneMonthAgo = $dateBefore->format("Y-m-d");

            $settlements = $this->client->getSettlements(Currency::USD, $oneMonthAgo, $today, null, null, null);
            $firstSettlement = $settlements[0];
            $settlement = $this->client->getSettlementReconciliationReport($firstSettlement);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($settlements);
        $this->assertGreaterThan(0, count($settlements));
        $this->assertNotNull($settlement->getId());
        $this->assertEquals($firstSettlement->getId(), $settlement->getId());
    }

    public function testShouldCreateSubscription()
    {
        $items = [];

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(30.0);
        $item->setQuantity(9);
        $item->setDescription("product-a");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(14.0);
        $item->setQuantity(16);
        $item->setDescription("product-b");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(3.90);
        $item->setQuantity(42);
        $item->setDescription("product-c");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(6.99);
        $item->setQuantity(12);
        $item->setDescription("product-d");
        array_push($items, $item);

        //Stop subscription a few days later
        $date = new \DateTime();
        $date->modify('+1 month');
        $dueDate = $date->format("Y-m-d");

        $billData = new BitPaySDK\Model\Subscription\BillData(
            Currency::USD,
            "sandbox@bitpay.com",
            $dueDate,
            $items
        );

        $subscription = new BitPaySDK\Model\Subscription\Subscription();
        $subscription->setBillData($billData);
        $subscription->setSchedule("weekly");
        $basicSubscription = null;
        try {
            $basicSubscription = $this->client->createSubscription($subscription);
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($basicSubscription->getId());
        $this->assertNotNull($basicSubscription->getBillData()->getItems()[0]);
    }

    public function testShouldGetSubscription()
    {
        $items = [];

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(30.0);
        $item->setQuantity(9);
        $item->setDescription("product-a");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(14.0);
        $item->setQuantity(16);
        $item->setDescription("product-b");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(3.90);
        $item->setQuantity(42);
        $item->setDescription("product-c");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(6.99);
        $item->setQuantity(12);
        $item->setDescription("product-d");
        array_push($items, $item);

        //Stop subscription a few days later
        $date = new \DateTime();
        $date->modify('+1 month');
        $dueDate = $date->format("Y-m-d");

        $billData = new BitPaySDK\Model\Subscription\BillData(
            Currency::USD,
            "sandbox@bitpay.com",
            $dueDate,
            $items
        );

        $subscription = new BitPaySDK\Model\Subscription\Subscription();
        $subscription->setBillData($billData);
        $subscription->setSchedule("weekly");
        $basicSubscription = null;
        $retrievedSubscription = null;
        try {
            $basicSubscription = $this->client->createSubscription($subscription);
            $retrievedSubscription = $this->client->getSubscription($basicSubscription->getId());
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertEquals($basicSubscription->getId(), $retrievedSubscription->getId());
        $this->assertEquals(
            $basicSubscription->getBillData()->getItems(), $retrievedSubscription->getBillData()->getItems());
    }

    public function testShouldUpdateSubscription()
    {
        $items = [];

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(30.0);
        $item->setQuantity(9);
        $item->setDescription("product-a");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(14.0);
        $item->setQuantity(16);
        $item->setDescription("product-b");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(3.90);
        $item->setQuantity(42);
        $item->setDescription("product-c");
        array_push($items, $item);

        $item = new BitPaySDK\Model\Subscription\Item();
        $item->setPrice(6.99);
        $item->setQuantity(12);
        $item->setDescription("product-d");
        array_push($items, $item);

        //Stop subscription a few days later
        $date = new \DateTime();
        $date->modify('+1 month');
        $dueDate = $date->format("Y-m-d");

        $billData = new BitPaySDK\Model\Subscription\BillData(
            Currency::USD,
            "sandbox@bitpay.com",
            $dueDate,
            $items
        );

        $subscription = new BitPaySDK\Model\Subscription\Subscription();
        $subscription->setBillData($billData);
        $subscription->setSchedule("weekly");
        $basicSubscription = null;
        $retrievedSubscription = null;
        $updatedSubscription = null;
        try {
            $basicSubscription = $this->client->createSubscription($subscription);
            $retrievedSubscription = $this->client->getSubscription($basicSubscription->getId());

            $this->assertEquals($basicSubscription->getId(), $retrievedSubscription->getId());
            $this->assertEquals(
                $basicSubscription->getBillData()->getItems(), $retrievedSubscription->getBillData()->getItems());
            $this->assertCount(4, $retrievedSubscription->getBillData()->getItems());

            $items = $retrievedSubscription->getBillData()->getItems();

            $item = new BitPaySDK\Model\Subscription\Item();
            $item->setPrice(60);
            $item->setQuantity(7);
            $item->setDescription("product-added");
            array_push($items, $item);

            $retrievedSubscription->getBillData()->setItems($items);
            $updatedSubscription = $this->client->updateSubscription(
                $retrievedSubscription, $retrievedSubscription->getId());
            $items = $updatedSubscription->getBillData()->getItems();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertCount(5, $updatedSubscription->getBillData()->getItems());
        $this->assertEquals(end($items)->getDescription(), "product-added");
    }

    public function testShouldGetSubscriptions()
    {
        $subscriptions = null;
        try {
            $subscriptions = $this->client->getSubscriptions();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertGreaterThan(0, count($subscriptions));
    }

    public function testShouldGetSubscriptionsByStatus()
    {
        $subscriptions = null;
        try {
            $subscriptions = $this->client->getSubscriptions();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertGreaterThan(0, count($subscriptions));
    }

    public function testShouldGetCurrencies()
    {
        $currencyList = null;
        try {
            $currencyList = $this->client->getCurrencies();
        } catch (\Exception $e) {
            $e->getTraceAsString();
            self::fail($e->getMessage());
        }

        $this->assertNotNull($currencyList);
    }
}
