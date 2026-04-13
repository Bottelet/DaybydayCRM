<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use App\Services\InvoiceNumber\InvoiceNumberService;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class InvoiceNumberServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $client;

    /**
     * @var Application
     */
    private $invoiceNumberService;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->user = User::factory()->create();

        // Ensure a Setting record exists for the service
        Setting::factory()->create();

        $this->client = Invoice::factory()->create([]);

        $this->invoiceNumberService = app(InvoiceNumberService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function it_sets_next_invoice_number_takes_biggest_invoice_number_and_add_one()
    {
        /** Arrange */
        // Service initialized with one invoice in database

        /** Act */
        $firstNumber  = $this->invoiceNumberService->setNextInvoiceNumber();
        $secondNumber = $this->invoiceNumberService->setNextInvoiceNumber();

        /* Assert */
        $this->assertEquals(10000, $firstNumber);
        $this->assertEquals(10001, $secondNumber);
    }

    #[Test]
    public function it_next_invoice_number_takes_biggest_invoice_number_and_does_not_add_one()
    {
        /** Arrange */
        // Service initialized with one invoice in database

        /** Act */
        $firstCall  = $this->invoiceNumberService->nextInvoiceNumber();
        $secondCall = $this->invoiceNumberService->nextInvoiceNumber();

        /* Assert */
        $this->assertEquals(10000, $firstCall);
        $this->assertEquals(10000, $secondCall);
    }

    #[Test]
    public function it_manually_set_next_invoice_number()
    {
        /** Arrange */
        $customNumber = 20000;

        /* Act */
        $this->invoiceNumberService->setInvoiceNumber($customNumber);
        $result = $this->invoiceNumberService->nextInvoiceNumber();

        /* Assert */
        $this->assertEquals(20000, $result);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_sets_next_invoice_number_with_multiple_existing_invoices()
    {
        /* Arrange */
        Invoice::factory()->create(['invoice_number' => 10005]);
        Invoice::factory()->create(['invoice_number' => 10003]);
        $service = app(InvoiceNumberService::class);

        /** Act */
        $nextNumber = $service->setNextInvoiceNumber();

        /* Assert */
        $this->assertEquals(10006, $nextNumber);
    }

    #[Test]
    public function it_sets_invoice_number_to_zero()
    {
        /** Arrange */
        $zeroNumber = 0;

        /* Act */
        $this->invoiceNumberService->setInvoiceNumber($zeroNumber);
        $result = $this->invoiceNumberService->nextInvoiceNumber();

        /* Assert */
        $this->assertEquals(0, $result);
    }

    #[Test]
    public function it_sets_next_invoice_number_increments_from_manually_set_number()
    {
        /* Arrange */
        $this->invoiceNumberService->setInvoiceNumber(15000);

        /** Act */
        $firstNumber  = $this->invoiceNumberService->setNextInvoiceNumber();
        $secondNumber = $this->invoiceNumberService->setNextInvoiceNumber();

        /* Assert */
        $this->assertEquals(15000, $firstNumber);
        $this->assertEquals(15001, $secondNumber);
    }

    # endregion
}
