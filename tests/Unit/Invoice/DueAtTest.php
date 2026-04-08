<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DueAtTest extends TestCase
{
    use DatabaseTransactions;

    protected $invoice;

    protected $secondInvoice;

    protected function setUp(): void
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

    #[Test]
    public function ensure_we_get_invoice_past_due_at()
    {
        $invoices = Invoice::pastDueAt()->get();

        $this->assertCount(1, $invoices);
        $this->assertEquals($this->secondInvoice->id, $invoices->first()->id);
    }

    #[Test]
    public function ensure_we_dont_get_invoice_if_due_at_is_null()
    {
        $this->secondInvoice->due_at = null;
        $this->secondInvoice->save();
        $invoices = Invoice::pastDueAt()->get();

        $this->assertCount(0, $invoices);
    }

    #[Test]
    public function ensure_we_dont_get_invoice_if_status_is_paid()
    {
        $invoices = Invoice::pastDueAt()->get();
        $this->assertCount(1, $invoices);

        $this->secondInvoice->status = 'paid';
        $this->secondInvoice->save();
        $invoices = Invoice::pastDueAt()->get();

        $this->assertCount(0, $invoices);
    }
}
