<?php
namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Services\Invoice\GenerateInvoiceStatus;
use App\Services\Invoice\InvoiceCalculator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InvoiceCalculatorTest extends TestCase
{
    use DatabaseTransactions;

    protected $invoice;
    protected $secondInvoice;

    public function setUp(): void
    {
        parent::setUp();
        $this->invoice = factory(Invoice::class)->create([
            'sent_at' => today(),
            'due_at' => today()->addDay(),
        ]);
        $this->secondInvoice = factory(Invoice::class)->create([
            'sent_at' => today(),
            'due_at' => today()->subDay(),
        ]);
    }

    /** @test */
    public function ensureWeGetInvoicePastDueAt()
    {
        $invoices =  Invoice::pastDueAt()->get();
        
        $this->assertCount(1, $invoices);
        $this->assertEquals($this->invoice->id, $invoices->first()->id);
    }

    /** @test */
    public function ensureWeDontInvoiceIfDueAtIsNull()
    {
        $this->invoice->due_at = null;
        $this->invoice->save();
        $invoices =  Invoice::pastDueAt()->get();
        
        $this->assertCount(0, $invoices);
    }
}
