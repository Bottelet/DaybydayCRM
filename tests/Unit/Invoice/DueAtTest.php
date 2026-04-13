<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class DueAtTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $invoice;

    protected $secondInvoice;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->invoice = Invoice::factory()->create([
            'sent_at' => Carbon::now(),
            'due_at'  => Carbon::now()->addDay(),
        ]);
        $this->secondInvoice = Invoice::factory()->create([
            'sent_at' => Carbon::now(),
            'due_at'  => Carbon::now()->subDay(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function it_gets_invoice_past_due_at()
    {
        /** Arrange */
        // Second invoice is past due (due_at is yesterday)

        /** Act */
        $invoices = Invoice::pastDueAt()->get();

        /* Assert */
        $this->assertCount(1, $invoices);
        $this->assertEquals($this->secondInvoice->id, $invoices->first()->id);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_dont_get_invoice_if_due_at_is_null()
    {
        /* Arrange */
        $this->secondInvoice->due_at = null;
        $this->secondInvoice->save();

        /** Act */
        $invoices = Invoice::pastDueAt()->get();

        /* Assert */
        $this->assertCount(0, $invoices);
    }

    #[Test]
    public function it_dont_get_invoice_if_status_is_paid()
    {
        /** Arrange */
        $invoices = Invoice::pastDueAt()->get();
        $this->assertCount(1, $invoices);

        $this->secondInvoice->status = 'paid';
        $this->secondInvoice->save();

        /** Act */
        $invoices = Invoice::pastDueAt()->get();

        /* Assert */
        $this->assertCount(0, $invoices);
    }

    #[Test]
    public function it_gets_multiple_invoices_past_due_at()
    {
        /** Arrange */
        $thirdInvoice = Invoice::factory()->create([
            'sent_at' => Carbon::now(),
            'due_at'  => Carbon::now()->subDays(5),
        ]);

        /** Act */
        $invoices = Invoice::pastDueAt()->get();

        /* Assert */
        $this->assertCount(2, $invoices);
    }

    #[Test]
    public function it_dont_get_invoice_due_today()
    {
        /* Arrange */
        $this->invoice->due_at = Carbon::now();
        $this->invoice->save();
        $this->secondInvoice->forceDelete();

        /** Act */
        $invoices = Invoice::pastDueAt()->get();

        /* Assert */
        $this->assertCount(0, $invoices);
    }

    #[Test]
    public function it_dont_get_invoice_due_in_future()
    {
        /* Arrange */
        $this->secondInvoice->due_at = Carbon::now()->addDays(3);
        $this->secondInvoice->save();

        /** Act */
        $invoices = Invoice::pastDueAt()->get();

        /* Assert */
        $this->assertCount(0, $invoices);
    }

    # endregion
}
