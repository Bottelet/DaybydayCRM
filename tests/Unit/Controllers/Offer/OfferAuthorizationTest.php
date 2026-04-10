<?php

namespace Tests\Unit\Controllers\Offer;

use App\Enums\OfferStatus;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('authorization-fix')]
class OfferAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Lead $lead;

    private Offer $offer;

    private User $userWithCreatePermission;

    private User $userWithEditPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lead = Lead::factory()->create();
        $this->offer = Offer::create([
            'source_id' => $this->lead->id,
            'source_type' => Lead::class,
            'client_id' => $this->lead->client_id,
            'status' => OfferStatus::inProgress()->getStatus(),
        ]);

        // Create or get the offer-create permission
        $createPermission = Permission::firstOrCreate(
            ['name' => 'offer-create'],
            [
                'display_name' => 'Create offer',
                'description' => 'Permission to create offer',
                'grouping' => 'offer',
                'external_id' => Str::uuid()->toString(),
            ]
        );

        // Create or get the offer-edit permission
        $editPermission = Permission::firstOrCreate(
            ['name' => 'offer-edit'],
            [
                'display_name' => 'Edit offer',
                'description' => 'Permission to edit offer',
                'grouping' => 'offer',
                'external_id' => Str::uuid()->toString(),
            ]
        );

        // Create role with offer-create permission
        $roleWithCreatePermission = Role::create([
            'name' => 'offer-creator',
            'display_name' => 'Offer Creator',
            'description' => 'Can create offers',
            'external_id' => Str::uuid()->toString(),
        ]);
        $roleWithCreatePermission->attachPermission($createPermission);

        // Create role with offer-edit permission
        $roleWithEditPermission = Role::create([
            'name' => 'offer-editor',
            'display_name' => 'Offer Editor',
            'description' => 'Can edit offers',
            'external_id' => Str::uuid()->toString(),
        ]);
        $roleWithEditPermission->attachPermission($editPermission);

        // Create role without any offer permissions
        $roleWithoutPermission = Role::create([
            'name' => 'offer-viewer',
            'display_name' => 'Offer Viewer',
            'description' => 'Cannot manage offers',
            'external_id' => Str::uuid()->toString(),
        ]);

        // Create users
        $this->userWithCreatePermission = User::factory()->create();
        $this->userWithCreatePermission->attachRole($roleWithCreatePermission);

        $this->userWithEditPermission = User::factory()->create();
        $this->userWithEditPermission->attachRole($roleWithEditPermission);

        $this->userWithoutPermission = User::factory()->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function user_with_offer_create_permission_can_create_offer()
    {
        $this->actingAs($this->userWithCreatePermission);

        $newLead = Lead::factory()->create();

        $response = $this->json('POST', route('create.offer', $newLead->external_id), [
            [
                'title' => 'Test Item',
                'type' => 'hours',
                'price' => 100,
                'quantity' => 1,
                'comment' => 'Test comment',
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('offers', ['source_id' => $newLead->id]);
    }

    #[Test]
    public function user_without_offer_create_permission_cannot_create_offer()
    {
        $this->actingAs($this->userWithoutPermission);

        $newLead = Lead::factory()->create();

        $response = $this->json('POST', route('create.offer', $newLead->external_id), [
            [
                'title' => 'Test Item',
                'type' => 'hours',
                'price' => 100,
                'quantity' => 1,
                'comment' => 'Test comment',
            ],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('offers', ['source_id' => $newLead->id]);
    }

    #[Test]
    public function user_with_offer_edit_permission_can_update_offer()
    {
        $this->actingAs($this->userWithEditPermission);

        $response = $this->json('POST', route('offer.update', $this->offer->external_id), [
            [
                'title' => 'Updated Item',
                'type' => 'hours',
                'price' => 200,
                'quantity' => 2,
                'comment' => 'Updated comment',
            ],
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function user_without_offer_edit_permission_cannot_update_offer()
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->json('POST', route('offer.update', $this->offer->external_id), [
            [
                'title' => 'Updated Item',
                'type' => 'hours',
                'price' => 200,
                'quantity' => 2,
                'comment' => 'Updated comment',
            ],
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function user_with_offer_edit_permission_can_mark_offer_as_won()
    {
        $this->actingAs($this->userWithEditPermission);

        $response = $this->json('POST', route('offer.won'), [
            'offer_external_id' => $this->offer->external_id,
        ]);

        $response->assertStatus(302);
        $this->assertEquals(OfferStatus::won()->getStatus(), $this->offer->refresh()->status);
        // Verify invoice was created
        $this->assertDatabaseHas('invoices', ['offer_id' => $this->offer->id]);
    }

    #[Test]
    public function user_without_offer_edit_permission_cannot_mark_offer_as_won()
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->json('POST', route('offer.won'), [
            'offer_external_id' => $this->offer->external_id,
        ]);

        $response->assertStatus(403);
        $this->assertEquals(OfferStatus::inProgress()->getStatus(), $this->offer->refresh()->status);
    }

    #[Test]
    public function user_with_offer_edit_permission_can_mark_offer_as_lost()
    {
        $this->actingAs($this->userWithEditPermission);

        $response = $this->json('POST', route('offer.lost'), [
            'offer_external_id' => $this->offer->external_id,
        ]);

        $response->assertStatus(302);
        $this->assertEquals(OfferStatus::lost()->getStatus(), $this->offer->refresh()->status);
    }

    #[Test]
    public function user_without_offer_edit_permission_cannot_mark_offer_as_lost()
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->json('POST', route('offer.lost'), [
            'offer_external_id' => $this->offer->external_id,
        ]);

        $response->assertStatus(403);
        $this->assertEquals(OfferStatus::inProgress()->getStatus(), $this->offer->refresh()->status);
    }
}
