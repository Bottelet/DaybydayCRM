<?php
namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Services\Invoice\GenerateInvoiceStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CanUpdateInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    private $invoice;

    public function setUp(): void
    {
        parent::setUp();
        $this->invoice = factory(Invoice::class)->create([
            'sent_at' => null
        ]);
    }

    /** @test */
    public function happyPath()
    {
        $this->assertTrue($this->invoice->canUpdateInvoice());
    }

    /** @test */
    public function cantUpdateInvoiceIfItsSent()
    {
        $this->invoice->sent_at = today();
        $this->invoice->save();

        $this->assertFalse($this->invoice->canUpdateInvoice());
    }
}
