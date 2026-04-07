<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\Lead;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RemoveReferenceTest extends TestCase
{
    use DatabaseTransactions;

    private $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoice = factory(Invoice::class)->create([
            'sent_at' => null,
            'integration_invoice_id' => factory(Lead::class)->create()->id,
            'integration_type' => Lead::class,
        ]);
    }

    /** @test */
    public function happy_path()
    {
        $this->assertNotNull($this->invoice->integration_invoice_id);
        $this->assertNotNull($this->invoice->integration_type);

        $this->invoice->removeReference();

        $this->assertNull($this->invoice->integration_invoice_id);
        $this->assertNull($this->invoice->integration_type);
    }
}
