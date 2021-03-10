<?php
namespace Tests\Unit\Controllers\Lead;

use Tests\TestCase;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Client;
use App\Models\Invoice;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeleteLeadControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $lead;
    private $offer;

    public function setUp(): void
    {
        parent::setUp();

        $this->lead = factory(Lead::class)->create();

        $this->offer = Offer::create([
            'source_id' => $this->lead->id,
            'source_type' => Lead::class,
            'client_id' => $this->lead->client_id,
        ]);
        
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @test */
    public function deleteLead()
    {
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id));
        
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
        
    }

    /** @test */
    public function deleteOffersIfFlagGiven()
    {   
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id), [
            'delete_offers' => "on"
        ]);
        
        
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
        $this->assertSoftDeleted('offers', ['id' => $this->offer->id]);
    }

    /** @test */
    public function doNotDeleteOffersIfFlagIsNotGivenButRemoveReference()
    {   
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id));

        $this->offer->refresh();

        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
        $this->assertNotNull(Offer::find($this->offer->id));
        $this->assertNull(Offer::find($this->offer->source_id));
    }


    /** @test */
    public function canDeleteLeadIfFlagIsGivenAndOffersDoesNotExists()
    {   
        $this->lead->offers()->forceDelete();
        
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id), [
            'delete_offers' => "on"
        ]);
        
        $this->assertNotNull($this->lead->refresh()->deleted_at);
    }
}
