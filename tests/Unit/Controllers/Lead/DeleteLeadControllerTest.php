<?php

namespace Tests\Unit\Controllers\Lead;

use App\Enums\OfferStatus;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Offer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteLeadControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $lead;

    private $offer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lead = factory(Lead::class)->create();

        $this->offer = Offer::create([
            'source_id' => $this->lead->id,
            'source_type' => Lead::class,
            'client_id' => $this->lead->client_id,
            'status' => OfferStatus::inProgress()->getStatus(),
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function delete_lead()
    {
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id));

        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);

    }

    #[Test]
    public function delete_offers_if_flag_given()
    {
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id), [
            'delete_offers' => 'on',
        ]);

        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
        $this->assertSoftDeleted('offers', ['id' => $this->offer->id]);
    }

    #[Test]
    public function do_not_delete_offers_if_flag_is_not_given_but_remove_reference()
    {
        $this->json('DELETE', route('leads.destroy', $this->lead->external_id));

        $this->offer->refresh();

        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
        $this->assertNotNull(Offer::find($this->offer->id));
        $this->assertNull(Offer::find($this->offer->source_id));
    }

    #[Test]
    public function can_delete_lead_if_flag_is_given_and_offers_does_not_exists()
    {
        $this->lead->offers()->forceDelete();

        $this->json('DELETE', route('leads.destroy', $this->lead->external_id), [
            'delete_offers' => 'on',
        ]);

        $this->assertNotNull($this->lead->refresh()->deleted_at);
    }
}
