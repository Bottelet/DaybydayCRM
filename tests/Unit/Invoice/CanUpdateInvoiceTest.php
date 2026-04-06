<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CanUpdateInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    private $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoice = factory(Invoice::class)->create([
            'sent_at' => null,
        ]);
    }

    /** @test */
    public function happy_path()
    {
        $this->assertTrue($this->invoice->canUpdateInvoice());
    }

    /** @test */
    public function cant_update_invoice_if_its_sent()
    {
        $this->invoice->sent_at = today();
        $this->invoice->save();

        $this->assertFalse($this->invoice->canUpdateInvoice());
    }
}
