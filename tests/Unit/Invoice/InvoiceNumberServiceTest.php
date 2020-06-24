<?php
namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Services\InvoiceNumber\InvoiceNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Testing\Fakes\EventFake;
use Tests\TestCase;

class InvoiceNumberServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $client;
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $invoiceNumberService;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->client = factory(Invoice::class)->create([

        ]);

        $this->invoiceNumberService = app(InvoiceNumberService::class);
    }

    /** @test */
    public function setNextInvoiceNumberTakesBiggestInvoiceNumberAndAddOne()
    {
        $this->assertEquals(10000, $this->invoiceNumberService->setNextInvoiceNumber());
        $this->assertEquals(10001, $this->invoiceNumberService->setNextInvoiceNumber());
    }

    /** @test */
    public function nextInvoiceNumberTakesBiggestInvoiceNumberAndDoesNotAddOne()
    {
        $this->assertEquals(10000, $this->invoiceNumberService->nextInvoiceNumber());
        $this->assertEquals(10000, $this->invoiceNumberService->nextInvoiceNumber());
    }

    /** @test */
    public function manuallySetNextInvoiceNumber()
    {
        $this->invoiceNumberService->setInvoiceNumber(20000);
        $this->assertEquals(20000, $this->invoiceNumberService->nextInvoiceNumber());
    }
}
