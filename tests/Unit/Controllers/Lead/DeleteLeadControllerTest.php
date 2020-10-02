<?php
namespace Tests\Unit\Controllers\Lead;

use Tests\TestCase;
use App\Models\Lead;
use App\Models\Client;
use App\Models\Invoice;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeleteLeadControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $lead;
    private $invoice;

    public function setUp(): void
    {
        parent::setUp();

        $this->lead = factory(Lead::class)->create();
        $this->invoice = factory(Invoice::class)->create([
            'status' => 'Test',
            'client_id' => factory(Client::class)->create()->id,
            'integration_invoice_id' => $this->lead->id,
            'integration_type' => Lead::class,
        ]);

        $this->lead->invoice_id = $this->invoice->id;
        $this->lead->save();
        
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @test */
    public function deleteLead()
    {
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id));
        
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
    }

    /** @test */
    public function deleteInvoiceIfFlagGiven()
    {   
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id), [
            'delete_invoice' => "on"
        ]);
        
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
        $this->assertSoftDeleted('invoices', ['id' => $this->invoice->id]);
    }

    /** @test */
    public function doNotDeleteInvoiceIfFlagIsNotGivenButRemoveReference()
    {   
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id));

        $this->assertNull($this->lead->invoice->refresh()->deleted_at);
        $this->assertNull($this->lead->invoice->refresh()->integration_invoice_id);
        $this->assertNull($this->lead->invoice->refresh()->integration_type);
    }


    /** @test */
    public function canDeleteLeadIfFlagIsGivenAndInvoiceDoesNotExists()
    {   
        $this->lead->invoice_id = null;
        $this->lead->save();
        
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id), [
            'delete_invoice' => "on"
        ]);
        
        $this->assertNotNull($this->lead->refresh()->deleted_at);
    }
}
