<?php

namespace Tests\Feature\Offers;

use App\Enums\OfferStatus;
use App\Http\Controllers\OffersController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(OffersController::class)]
class OffersTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $lead;

    protected $offer;

    protected $user;

    private User $userWithCreatePermission;

    private User $userWithEditPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role       = Role::query()->firstOrCreate(['name' => 'employee']);

        $createPermission = Permission::query()->firstOrCreate(['name' => 'offer-create']);
        $editPermission   = Permission::query()->firstOrCreate(['name' => 'offer-edit']);

        $role->attachPermission($createPermission);
        $role->attachPermission($editPermission);

        $this->user->attachRole($role);

        Cache::flush();

        $this->user = $this->user->fresh();

        $this->actingAs($this->user);

        $this->lead  = Lead::factory()->create();
        $this->offer = Offer::query()->create([
            'source_id'   => $this->lead->id,
            'source_type' => Lead::class,
            'client_id'   => $this->lead->client_id,
            'status'      => OfferStatus::inProgress()->getStatus(),
        ]);

        $createPermission = Permission::query()->firstOrCreate(
            ['name' => 'offer-create'],
            [
                'display_name' => 'Create offer',
                'description'  => 'Permission to create offer',
                'grouping'     => 'offer',
                'external_id'  => Str::uuid()->toString(),
            ]
        );

        $editPermission = Permission::query()->firstOrCreate(
            ['name' => 'offer-edit'],
            [
                'display_name' => 'Edit offer',
                'description'  => 'Permission to edit offer',
                'grouping'     => 'offer',
                'external_id'  => Str::uuid()->toString(),
            ]
        );

        $roleWithCreatePermission = Role::query()->create([
            'name'         => 'offer-creator',
            'display_name' => 'Offers Creator',
            'description'  => 'Can create offers',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $roleWithCreatePermission->attachPermission($createPermission);

        $roleWithEditPermission = Role::query()->create([
            'name'         => 'offer-editor',
            'display_name' => 'Offers Editor',
            'description'  => 'Can edit offers',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $roleWithEditPermission->attachPermission($editPermission);

        $roleWithoutPermission = Role::query()->create([
            'name'         => 'offer-viewer',
            'display_name' => 'Offers Viewer',
            'description'  => 'Cannot manage offers',
            'external_id'  => Str::uuid()->toString(),
        ]);

        $this->userWithCreatePermission = User::factory()->create();
        $this->userWithCreatePermission->attachRole($roleWithCreatePermission);

        $this->userWithEditPermission = User::factory()->create();
        $this->userWithEditPermission->attachRole($roleWithEditPermission);

        $this->userWithoutPermission = User::factory()->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->lead  = Lead::factory()->create();
        $this->offer = Offer::factory()->create();
    }

    #[Test]
    #[Group('keeps_failing')]
    public function it_can_create_offer()
    {
        /* Arrange */
        /* Act */
        $this->post(route('create.offer', $this->lead->external_id), [
            [
                'title'    => 'test line',
                'price'    => 1000,
                'quantity' => 2,
                'type'     => 'pieces',
                'comment'  => 'A comment',
                'product'  => '',
            ],
        ]);

        /* Assert */
        $this->lead->refresh();

        $this->assertNotEmpty($this->lead->offers);
        $this->assertNotEmpty($this->lead->offers->first()->invoiceLines);

        $this->assertEquals($this->lead->offers->first()->source_id, $this->lead->id);
        $this->assertEquals($this->lead->offers->first()->source_type, Lead::class);
    }

    #[Test]
    public function it_can_create_offer_for_client()
    {
        /* Arrange */
        $client = Client::factory()->create();

        /* Act */
        $response = $this->post(route('create.offer', $client->external_id), [
            [
                'title'    => 'client offer line',
                'price'    => 1000,
                'quantity' => 1,
                'type'     => 'pieces',
                'comment'  => 'Client level offer',
                'product'  => '',
            ],
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertDatabaseHas('offers', [
            'client_id'   => $client->id,
            'source_id'   => $client->id,
            'source_type' => Client::class,
        ]);

        $offer = Offer::query()
            ->where('client_id', $client->id)
            ->where('source_id', $client->id)
            ->where('source_type', Client::class)
            ->latest('id')
            ->first();

        $this->assertNotNull($offer);
        $this->assertDatabaseHas('invoice_lines', [
            'offer_id' => $offer->id,
            'title'    => 'client offer line',
            'type'     => 'pieces',
            'quantity' => 1,
            'price'    => 100000,
            'comment'  => 'Client level offer',
        ]);
    }

    #[Test]
    public function it_user_with_offer_create_permission_can_create_offer()
    {
        /* Arrange */
        $this->actingAs($this->userWithCreatePermission);

        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->userWithCreatePermission = $this->userWithCreatePermission->fresh();

        $newLead = Lead::factory()->create();

        /* Act */
        $response = $this->post(route('create.offer', $newLead->external_id), [
            [
                'title'    => 'Test Item',
                'type'     => 'hours',
                'price'    => 100,
                'quantity' => 1,
                'comment'  => 'Test comment',
            ],
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertDatabaseHas('offers', ['source_id' => $newLead->id]);
    }

    #[Test]
    public function it_user_without_offer_create_permission_cannot_create_offer()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        $newLead = Lead::factory()->create();

        /* Act */
        $response = $this->post(route('create.offer', $newLead->external_id), [
            [
                'title'    => 'Test Item',
                'type'     => 'hours',
                'price'    => 100,
                'quantity' => 1,
                'comment'  => 'Test comment',
            ],
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseMissing('offers', ['source_id' => $newLead->id]);
    }

    #[Test]
    public function it_user_with_offer_edit_permission_can_update_offer()
    {
        /* Arrange */
        $this->actingAs($this->userWithEditPermission);

        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->userWithEditPermission = $this->userWithEditPermission->fresh();

        /* Act */
        $response = $this->post(route('offer.update', $this->offer->external_id), [
            [
                'title'    => 'Updated Item',
                'type'     => 'hours',
                'price'    => 200,
                'quantity' => 2,
                'comment'  => 'Updated comment',
            ],
        ]);

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_user_without_offer_edit_permission_cannot_update_offer()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->post(route('offer.update', $this->offer->external_id), [
            [
                'title'    => 'Updated Item',
                'type'     => 'hours',
                'price'    => 200,
                'quantity' => 2,
                'comment'  => 'Updated comment',
            ],
        ]);

        /* Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_user_with_offer_edit_permission_can_mark_offer_as_won()
    {
        /* Arrange */
        $this->actingAs($this->userWithEditPermission);

        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->userWithEditPermission = $this->userWithEditPermission->fresh();

        /* Act */
        $response = $this->post(route('offer.won'), [
            'offer_external_id' => $this->offer->external_id,
        ]);

        /* Assert */
        $response->assertStatus(302);
        $this->assertEquals(OfferStatus::won()->getStatus(), $this->offer->refresh()->status);
        $this->assertDatabaseHas('invoices', ['offer_id' => $this->offer->id]);
    }

    #[Test]
    public function it_user_without_offer_edit_permission_cannot_mark_offer_as_won()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->post(route('offer.won'), [
            'offer_external_id' => $this->offer->external_id,
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertEquals(OfferStatus::inProgress()->getStatus(), $this->offer->refresh()->status);
    }

    #[Test]
    public function it_user_with_offer_edit_permission_can_mark_offer_as_lost()
    {
        /* Arrange */
        $this->actingAs($this->userWithEditPermission);

        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->userWithEditPermission = $this->userWithEditPermission->fresh();

        /* Act */
        $response = $this->post(route('offer.lost'), [
            'offer_external_id' => $this->offer->external_id,
        ]);

        /* Assert */
        $response->assertStatus(302);
        $this->assertEquals(OfferStatus::lost()->getStatus(), $this->offer->refresh()->status);
    }

    #[Test]
    public function it_user_without_offer_edit_permission_cannot_mark_offer_as_lost()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->post(route('offer.lost'), [
            'offer_external_id' => $this->offer->external_id,
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertEquals(OfferStatus::inProgress()->getStatus(), $this->offer->refresh()->status);
    }

    #[Test]
    #[Group('keeps_failing')]
    public function it_can_update_offer()
    {
        /* Arrange */
        $this->assertCount(0, $this->offer->invoiceLines);

        /* Act */
        $this->post(route('offer.update', $this->offer->external_id), [
            [
                'title'    => 'test line',
                'price'    => 1000,
                'quantity' => 4,
                'type'     => 'pieces',
                'comment'  => 'A comment',
                'product'  => '',
            ],
            [
                'title'    => 'test line',
                'price'    => 1000,
                'quantity' => 4,
                'type'     => 'pieces',
                'comment'  => 'A comment',
                'product'  => '',
            ],
            [
                'title'    => 'test line',
                'price'    => 1000,
                'quantity' => 4,
                'type'     => 'pieces',
                'comment'  => 'A comment',
                'product'  => '',
            ],
        ]);

        /* Assert */
        $this->offer->refresh();

        $this->assertCount(3, $this->offer->invoiceLines);
    }

    #[Test]
    public function it_can_set_offer_as_won()
    {
        /* Arrange */
        $offer = Offer::factory()->create();

        /* Act */
        $this->post(route('offer.won'), [
            'offer_external_id' => $offer->external_id,
        ]);

        /* Assert */
        $offer->refresh();

        $this->assertEquals('won', $offer->status);
        $this->assertNotNull($offer->invoice);
    }

    #[Test]
    public function it_can_set_offer_as_lost()
    {
        /* Arrange */
        $offer = Offer::factory()->create();

        /* Act */
        $this->post(route('offer.lost'), [
            'offer_external_id' => $offer->external_id,
        ]);

        /* Assert */
        $offer->refresh();

        $this->assertEquals('lost', $offer->status);
        $this->assertNull($offer->invoice);
    }

    #[Test]
    public function it_returns_web_error_when_offer_creation_throws_exception()
    {
        /* Arrange */
        $client = Client::factory()->create();

        /* Act */
        $response = $this->from(route('clients.show', $client->external_id))
            ->post(route('create.offer', $client->external_id), [
                [
                    'title'    => 'line with bad product',
                    'price'    => 1000,
                    'quantity' => 1,
                    'type'     => 'pieces',
                    'comment'  => 'bad product',
                    'product'  => 'missing-product-external-id',
                ],
            ]);

        /* Assert */
        $response->assertRedirect(route('clients.show', $client->external_id));
        $response->assertSessionHasErrors(['0.product']);
    }

    #[Test]
    public function it_returns_json_error_when_offer_creation_throws_exception()
    {
        /* Arrange */
        $client = Client::factory()->create();

        /* Act */
        $response = $this->post(route('create.offer', $client->external_id), [
            [
                'title'    => 'line with bad product',
                'price'    => 1000,
                'quantity' => 1,
                'type'     => 'pieces',
                'comment'  => 'bad product',
                'product'  => 'missing-product-external-id',
            ],
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['0.product']);
    }
}
