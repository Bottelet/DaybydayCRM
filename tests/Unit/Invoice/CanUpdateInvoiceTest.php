<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CanUpdateInvoiceTest extends TestCase
{
    use DatabaseTransactions;

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
