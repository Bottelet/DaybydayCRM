<?php
namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DueAtTest extends TestCase
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
        $invoices = Invoice::pastDueAt()->get();
        
        $this->assertCount(1, $invoices);
        $this->assertEquals($this->secondInvoice->id, $invoices->first()->id);
    }

    /** @test */
    public function ensureWeDontGetInvoiceIfDueAtIsNull()
    {
        $this->secondInvoice->due_at = null;
        $this->secondInvoice->save();
        $invoices =  Invoice::pastDueAt()->get();
        
        $this->assertCount(0, $invoices);
    }

    /** @test */
    public function ensureWeDontGetInvoiceIfStatusIsPaid()
    {
        $invoices =  Invoice::pastDueAt()->get();
        $this->assertCount(1, $invoices);

        $this->secondInvoice->status = "paid";
        $this->secondInvoice->save();
        $invoices =  Invoice::pastDueAt()->get();
        
        $this->assertCount(0, $invoices);
    }
}
