<?php

namespace Tests\Unit\Invoice;

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

        // Freeze time for deterministic tests
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

    # region happy_path

    #[Test]
    public function it_can_update_draft_invoice()
    {
        /** Arrange */
        // Invoice created with sent_at = null in setUp()

        /** Act */
        $result = $this->invoice->canUpdateInvoice();

        /* Assert */
        $this->assertTrue($result);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_cant_update_invoice_if_its_sent()
    {
        /* Arrange */
        $this->invoice->sent_at = Carbon::now();
        $this->invoice->save();

        /** Act */
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

        /** Act */
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

        /** Act */
        $result = $this->invoice->canUpdateInvoice();

        /* Assert */
        $this->assertTrue($result);
    }

    # endregion
}
