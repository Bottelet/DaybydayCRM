<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CanUpdateInvoiceAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoice = Invoice::factory()->create([
            'sent_at' => null,
        ]);
    }

    #[Test]
    public function happy_path()
    {
        $this->assertTrue($this->invoice->canUpdateInvoice());
    }

    #[Test]
    public function cant_update_invoice_if_its_sent()
    {
        $this->invoice->sent_at = today();
        $this->invoice->save();

        $this->assertFalse($this->invoice->canUpdateInvoice());
    }
}
