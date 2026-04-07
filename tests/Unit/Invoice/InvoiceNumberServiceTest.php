<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoiceNumber\InvoiceNumberService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InvoiceNumberServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $client;

    /**
     * @var Application
     */
    private $invoiceNumberService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->client = factory(Invoice::class)->create([

        ]);

        $this->invoiceNumberService = app(InvoiceNumberService::class);
    }

    /** @test */
    public function set_next_invoice_number_takes_biggest_invoice_number_and_add_one()
    {
        $this->assertEquals(10000, $this->invoiceNumberService->setNextInvoiceNumber());
        $this->assertEquals(10001, $this->invoiceNumberService->setNextInvoiceNumber());
    }

    /** @test */
    public function next_invoice_number_takes_biggest_invoice_number_and_does_not_add_one()
    {
        $this->assertEquals(10000, $this->invoiceNumberService->nextInvoiceNumber());
        $this->assertEquals(10000, $this->invoiceNumberService->nextInvoiceNumber());
    }

    /** @test */
    public function manually_set_next_invoice_number()
    {
        $this->invoiceNumberService->setInvoiceNumber(20000);
        $this->assertEquals(20000, $this->invoiceNumberService->nextInvoiceNumber());
    }
}
