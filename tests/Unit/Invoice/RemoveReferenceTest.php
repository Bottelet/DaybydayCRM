<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\Lead;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RemoveReferenceTest extends TestCase
{
    use RefreshDatabase;

    private $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoice = Invoice::factory()->create([
            'sent_at' => null,
            'integration_invoice_id' => Lead::factory()->create()->id,
            'integration_type' => Lead::class,
        ]);
    }

    #[Test]
    public function happy_path()
    {
        $this->assertNotNull($this->invoice->integration_invoice_id);
        $this->assertNotNull($this->invoice->integration_type);

        $this->invoice->removeReference();

        $this->assertNull($this->invoice->integration_invoice_id);
        $this->assertNull($this->invoice->integration_type);
    }
}
