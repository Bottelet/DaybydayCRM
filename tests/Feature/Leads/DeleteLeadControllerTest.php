<?php

namespace Tests\Feature\Leads;

use App\Enums\OfferStatus;
use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class DeleteLeadControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role       = Role::query()->firstOrCreate(
            ['name' => 'employee'],
            [
                'display_name' => 'Employee',
                'description'  => 'Employee role',
                'external_id'  => Str::uuid()->toString(),
            ]
        );
        $this->user->attachRole($role);
        $this->withPermissions(PermissionName::LEAD_DELETE);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_deletes_lead()
    {
        /* Arrange */
        $lead = Lead::factory()->create();

        /* Act */
        $response = $this->json('DELETE', route('leads.destroy', $lead->external_id));

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }

    #[Test]
    public function it_deletes_offers_if_flag_given()
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $offer = Offer::create([
            'source_id'   => $lead->id,
            'source_type' => Lead::class,
            'client_id'   => $lead->client_id,
            'status'      => OfferStatus::inProgress()->getStatus(),
        ]);

        /* Act */
        $response = $this->json('DELETE', route('leads.destroy', $lead->external_id), [
            'delete_offers' => 'on',
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
        $this->assertSoftDeleted('offers', ['id' => $offer->id]);
    }

    #[Test]
    public function it_does_not_delete_offers_if_flag_is_not_given_but_remove_reference()
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $offer = Offer::create([
            'source_id'   => $lead->id,
            'source_type' => Lead::class,
            'client_id'   => $lead->client_id,
            'status'      => OfferStatus::inProgress()->getStatus(),
        ]);

        /* Act */
        $response = $this->json('DELETE', route('leads.destroy', $lead->external_id));
        $offer->refresh();

        /* Assert */
        $response->assertStatus(200);
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
        $this->assertNotNull(Offer::find($offer->id));
        $this->assertNull(Offer::find($offer->source_id));
    }

    #[Test]
    public function it_can_delete_lead_if_flag_is_given_and_offers_does_not_exists()
    {
        /* Arrange */
        $lead = Lead::factory()->create();
        $lead->offers()->forceDelete();

        /* Act */
        $response = $this->json('DELETE', route('leads.destroy', $lead->external_id), [
            'delete_offers' => 'on',
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertNotNull($lead->refresh()->deleted_at);
    }
}
