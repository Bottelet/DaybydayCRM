<?php
namespace Tests\Unit\Invoice;

use Tests\TestCase;
use App\Models\Lead;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RemoveReferenceTest extends TestCase
{
    use DatabaseTransactions;

    private $invoice;

    public function setUp(): void
    {
        parent::setUp();
        $this->invoice = factory(Invoice::class)->create([
            'sent_at' => null,
            'integration_invoice_id' => factory(Lead::class)->create()->id,
            'integration_type' => Lead::class,
        ]);
    }

    /** @test */
    public function happyPath()
    {
        $this->assertNotNull($this->invoice->integration_invoice_id);
        $this->assertNotNull($this->invoice->integration_type);

        $this->invoice->removeReference();

        $this->assertNull($this->invoice->integration_invoice_id);
        $this->assertNull($this->invoice->integration_type);
    }
}
