<?php

namespace Tests\Feature\Invoices;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class InvoiceLinesControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    private $invoiceLine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice     = Invoice::factory()->create();
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
        ]);
    }

    #[Test]
    public function it_happy_path()
    {
        /* Arrange */
        $permission = Permission::query()->firstOrCreate(
            ['name' => 'modify-invoice-lines'],
            [
                'display_name' => 'Modify invoice lines',
                'description'  => 'Permission to modify invoice lines',
                'external_id'  => Str::uuid()->toString(),
            ]
        );

        $ownerRole = Role::query()->firstOrCreate(
            ['name' => 'owner'],
            [
                'display_name' => 'Owner',
                'description'  => 'Owner role',
                'external_id'  => Str::uuid()->toString(),
            ]
        );

        if ( ! $ownerRole->hasPermission('modify-invoice-lines')) {
            $ownerRole->attachPermission($permission);
        }

        if ( ! $this->user->hasRole('owner')) {
            $this->user->attachRole($ownerRole);
        }

        Cache::tags('role_user')->flush();
        $this->user = $this->user->fresh();
        $this->actingAs($this->user);

        $this->assertNotNull(InvoiceLine::query()->where('external_id', $this->invoiceLine->external_id)->first());

        /* Act */
        $r = $this->json('delete', route('invoiceLine.destroy', $this->invoiceLine->external_id));

        /* Assert */
        $r->assertStatus(302);
        $this->assertSoftDeleted('invoice_lines', ['external_id' => $this->invoiceLine->external_id]);
    }

    #[Test]
    public function it_cant_delete_without_permission()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertNotNull(InvoiceLine::query()->where('external_id', $this->invoiceLine->external_id)->first());

        /* Act */
        $response = $this->json('delete', route('invoiceLine.destroy', $this->invoiceLine->external_id));

        /* Assert */
        $response->assertStatus(403);
        $response->assertJson(['message' => __('You do not have permission to modify invoice lines')]);
        $this->assertNotNull(InvoiceLine::query()->where('external_id', $this->invoiceLine->external_id)->first());
    }
}
