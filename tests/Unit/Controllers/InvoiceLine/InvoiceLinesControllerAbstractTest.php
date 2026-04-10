<?php

namespace Tests\Unit\Controllers\InvoiceLine;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceLinesControllerAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    private $invoiceLine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice = Invoice::factory()->create();
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
        ]);
    }

    #[Test]
    public function happy_path()
    {
        // Ensure the permission exists and is attached to the user's role
        $permission = Permission::firstOrCreate(
            ['name' => 'modify-invoice-lines'],
            [
                'display_name' => 'Modify invoice lines',
                'description' => 'Permission to modify invoice lines',
                'external_id' => Str::uuid()->toString(),
            ]
        );

        $ownerRole = Role::whereName('owner')->first();
        if ($ownerRole) {
            $ownerRole->attachPermission($permission);
            $this->user->attachRole($ownerRole);
        }

        // Explicitly clear the permissions cache
        Cache::tags('role_user')->flush();

        $this->assertNotNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());

        $r = $this->json('delete', route('invoiceLine.destroy', $this->invoiceLine->external_id));

        $r->assertStatus(302);
        $this->assertSoftDeleted('invoice_lines', ['external_id' => $this->invoiceLine->external_id]);
    }

    #[Test]
    public function cant_delete_without_permission()
    {
        $user = User::factory()->create();
        $this->setUser($user);
        $this->assertNotNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());

        $response = $this->json('delete', route('invoiceLine.destroy', $this->invoiceLine->external_id));

        $response->assertStatus(302);
        $response->assertSessionHas('flash_message_warning');
        $this->assertNotNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());
    }
}
