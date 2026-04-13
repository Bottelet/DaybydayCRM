<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class RemoveReferenceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->invoice = Invoice::factory()->create([
            'sent_at'                => null,
            'integration_invoice_id' => Lead::factory()->create()->id,
            'integration_type'       => Lead::class,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function it_removes_reference_clears_integration_fields()
    {
        /* Arrange */
        // Invoice created with integration reference in setUp()
        $this->assertNotNull($this->invoice->integration_invoice_id);
        $this->assertNotNull($this->invoice->integration_type);

        /* Act */
        $this->invoice->removeReference();

        /* Assert */
        $this->assertNull($this->invoice->integration_invoice_id);
        $this->assertNull($this->invoice->integration_type);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_removes_reference_on_invoice_with_project_reference()
    {
        /** Arrange */
        $project = Project::factory()->create();
        $invoice = Invoice::factory()->create([
            'sent_at'                => null,
            'integration_invoice_id' => $project->id,
            'integration_type'       => Project::class,
        ]);

        /* Act */
        $invoice->removeReference();

        /* Assert */
        $this->assertNull($invoice->integration_invoice_id);
        $this->assertNull($invoice->integration_type);
    }

    #[Test]
    public function it_removes_reference_on_invoice_without_reference()
    {
        /** Arrange */
        $invoice = Invoice::factory()->create([
            'sent_at'                => null,
            'integration_invoice_id' => null,
            'integration_type'       => null,
        ]);

        /* Act */
        $invoice->removeReference();

        /* Assert */
        $this->assertNull($invoice->integration_invoice_id);
        $this->assertNull($invoice->integration_type);
    }

    #[Test]
    public function it_removes_reference_persists_to_database()
    {
        /** Arrange */
        $invoiceId = $this->invoice->id;

        /* Act */
        $this->invoice->removeReference();

        /** Assert */
        $freshInvoice = Invoice::find($invoiceId);
        $this->assertNull($freshInvoice->integration_invoice_id);
        $this->assertNull($freshInvoice->integration_type);
    }

    # endregion
}
