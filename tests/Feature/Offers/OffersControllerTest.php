<?php

namespace Tests\Feature\Offers;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class OffersControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $lead;

    protected $offer;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $role       = Role::firstOrCreate(['name' => 'employee']);

        $createPermission = Permission::firstOrCreate(['name' => 'offer-create']);
        $editPermission   = Permission::firstOrCreate(['name' => 'offer-edit']);

        $role->attachPermission($createPermission);
        $role->attachPermission($editPermission);

        $this->user->attachRole($role);

        \Illuminate\Support\Facades\Cache::flush();

        $this->user = $this->user->fresh();

        $this->actingAs($this->user);

        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->lead  = Lead::factory()->create();
        $this->offer = Offer::factory()->create();
    }

    #[Test]
    #[Group('keeps_failing')]
    public function it_can_create_offer()
    {
        /* Arrange */
        /* Act */
        $this->json('POST', route('create.offer', $this->lead->external_id), [
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
        $response = $this->json('POST', route('create.offer', $client->external_id), [
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
        $response = $this->json('POST', route('create.offer', $client->external_id), [
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
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['0.product']);
    }

    #[Test]
    #[Group('keeps_failing')]
    public function it_can_update_offer()
    {
        /* Arrange */
        $this->assertCount(0, $this->offer->invoiceLines);

        /* Act */
        $this->json('POST', route('offer.update', $this->offer->external_id), [
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
        $this->json('POST', route('offer.won'), [
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
        $this->json('POST', route('offer.lost'), [
            'offer_external_id' => $offer->external_id,
        ]);

        /* Assert */
        $offer->refresh();

        $this->assertEquals('lost', $offer->status);
        $this->assertNull($offer->invoice);
    }
}
