<?php

namespace Tests\Unit\Invoices;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class CanUpdateInvoiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->invoice = Invoice::factory()->create([
            'sent_at' => null,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_can_update_draft_invoice()
    {
        /* Arrange */

        /* Act */
        $result = $this->invoice->canUpdateInvoice();

        /* Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function it_cant_update_invoice_if_its_sent()
    {
        /* Arrange */
        $this->invoice->sent_at = Carbon::now();
        $this->invoice->save();

        /* Act */
        $result = $this->invoice->canUpdateInvoice();

        /* Assert */
        $this->assertFalse($result);
    }

    #[Test]
    public function it_cant_update_invoice_sent_in_the_past()
    {
        /* Arrange */
        $this->invoice->sent_at = Carbon::now()->subDays(5);
        $this->invoice->save();

        /* Act */
        $result = $this->invoice->canUpdateInvoice();

        /* Assert */
        $this->assertFalse($result);
    }

    #[Test]
    public function it_can_update_invoice_with_null_sent_at()
    {
        /* Arrange */
        $this->invoice->sent_at = null;
        $this->invoice->save();

        /* Act */
        $result = $this->invoice->canUpdateInvoice();

        /* Assert */
        $this->assertTrue($result);
    }
}
