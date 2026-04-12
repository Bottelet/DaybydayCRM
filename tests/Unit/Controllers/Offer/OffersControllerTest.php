<?php

namespace Tests\Unit\Controllers\Offer;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $role = Role::firstOrCreate(['name' => 'employee']);

        // Attach both create and edit permissions
        $createPermission = Permission::firstOrCreate(['name' => 'offer-create']);
        $editPermission = Permission::firstOrCreate(['name' => 'offer-edit']);

        $role->attachPermission($createPermission);
        $role->attachPermission($editPermission);

        $this->user->attachRole($role);

        // Clear permission cache AFTER attaching permissions
        \Illuminate\Support\Facades\Cache::flush();

        // Refresh user to reload roles and permissions
        $this->user = $this->user->fresh();

        // MUST call actingAs AFTER refresh to ensure permission check works
        $this->actingAs($this->user);

        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->lead = Lead::factory()->create();
        $this->offer = Offer::factory()->create();
    }

    #[Test]
    #[Group('keeps_failing')]
    public function can_create_offer()
    {
        $this->json('POST', route('create.offer', $this->lead->external_id), [
            [
                'title' => 'test line',
                'price' => 1000,
                'quantity' => 2,
                'type' => 'pieces',
                'comment' => 'A comment',
                'product' => '',
            ],
        ]);

        $this->lead->refresh();

        $this->assertNotEmpty($this->lead->offers);
        $this->assertNotEmpty($this->lead->offers->first()->invoiceLines);

        $this->assertEquals($this->lead->offers->first()->source_id, $this->lead->id);
        $this->assertEquals($this->lead->offers->first()->source_type, Lead::class);

    }

    #[Test]
    #[Group('keeps_failing')]
    public function can_update_offer()
    {
        $this->assertCount(0, $this->offer->invoiceLines);
        $this->json('POST', route('offer.update', $this->offer->external_id), [
            [
                'title' => 'test line',
                'price' => 1000,
                'quantity' => 4,
                'type' => 'pieces',
                'comment' => 'A comment',
                'product' => '',
            ],
            [
                'title' => 'test line',
                'price' => 1000,
                'quantity' => 4,
                'type' => 'pieces',
                'comment' => 'A comment',
                'product' => '',
            ],
            [
                'title' => 'test line',
                'price' => 1000,
                'quantity' => 4,
                'type' => 'pieces',
                'comment' => 'A comment',
                'product' => '',
            ],
        ]);

        $this->offer->refresh();

        $this->assertCount(3, $this->offer->invoiceLines);
    }

    #[Test]
    public function can_set_offer_as_won()
    {
        $offer = Offer::factory()->create();

        $this->json('POST', route('offer.won'), [
            'offer_external_id' => $offer->external_id,
        ]);

        $offer->refresh();

        $this->assertEquals('won', $offer->status);
        $this->assertNotNull($offer->invoice);
    }

    #[Test]
    public function can_set_offer_as_lost()
    {
        $offer = Offer::factory()->create();

        $this->json('POST', route('offer.lost'), [
            'offer_external_id' => $offer->external_id,
        ]);

        $offer->refresh();

        $this->assertEquals('lost', $offer->status);
        $this->assertNull($offer->invoice);
    }
}
