<?php

namespace Tests\Unit\Controllers\Lead;

use App\Enums\OfferStatus;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteLeadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role = Role::firstOrCreate(
            ['name' => 'employee'],
            [
                'display_name' => 'Employee',
                'description' => 'Employee role',
                'external_id' => Str::uuid()->toString(),
            ]
        );
        $permission = Permission::firstOrCreate(
            ['name' => 'lead-delete'],
            [
                'display_name' => 'Delete leads',
                'description' => 'Permission to delete leads',
                'external_id' => Str::uuid()->toString(),
            ]
        );
        $role->attachPermission($permission);
        $this->user->attachRole($role);

        // Explicitly clear the permissions cache
        Cache::tags('role_user')->flush();

        $this->actingAs($this->user);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function delete_lead()
    {
        $lead = Lead::factory()->create();

        $this->json('DELETE', route('leads.destroy', $lead->external_id));

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }

    #[Test]
    public function delete_offers_if_flag_given()
    {
        $lead = Lead::factory()->create();
        $offer = Offer::create([
            'source_id' => $lead->id,
            'source_type' => Lead::class,
            'client_id' => $lead->client_id,
            'status' => OfferStatus::inProgress()->getStatus(),
        ]);

        $this->json('DELETE', route('leads.destroy', $lead->external_id), [
            'delete_offers' => 'on',
        ]);

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
        $this->assertSoftDeleted('offers', ['id' => $offer->id]);
    }

    #[Test]
    public function do_not_delete_offers_if_flag_is_not_given_but_remove_reference()
    {
        $lead = Lead::factory()->create();
        $offer = Offer::create([
            'source_id' => $lead->id,
            'source_type' => Lead::class,
            'client_id' => $lead->client_id,
            'status' => OfferStatus::inProgress()->getStatus(),
        ]);

        $this->json('DELETE', route('leads.destroy', $lead->external_id));

        $offer->refresh();

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
        $this->assertNotNull(Offer::find($offer->id));
        $this->assertNull(Offer::find($offer->source_id));
    }

    #[Test]
    public function can_delete_lead_if_flag_is_given_and_offers_does_not_exists()
    {
        $lead = Lead::factory()->create();
        $lead->offers()->forceDelete();

        $this->json('DELETE', route('leads.destroy', $lead->external_id), [
            'delete_offers' => 'on',
        ]);

        $this->assertNotNull($lead->refresh()->deleted_at);
    }
}
