<?php

namespace Tests\Unit\Controllers\InvoiceLine;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceLinesControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $invoice;

    private $invoiceLine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice = factory(Invoice::class)->create();
        $this->invoiceLine = factory(InvoiceLine::class)->create([
            'invoice_id' => $this->invoice->id,
        ]);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function happy_path()
    {
        $this->user->attachRole(Role::whereName('owner')->first());

        $role = $this->user->roles->first();
        if ($role) {
            $permission = Permission::where('name', 'modify-invoice-lines')->first();
            if ($permission && ! $role->hasPermission($permission->name)) {
                $role->attachPermission($permission);
            }
        }

        $this->assertNotNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());

        $r = $this->json('delete', route('invoiceLine.destroy', $this->invoiceLine->external_id));

        $r->assertStatus(302);
        $this->assertNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());
    }

    #[Test]
    public function cant_delete_without_permission()
    {
        $user = factory(User::class)->create();
        $this->setUser($user);
        $this->assertNotNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());

        $response = $this->json('delete', route('invoiceLine.destroy', $this->invoiceLine->external_id));

        $response->assertStatus(302);
        $response->assertSessionHas('flash_message_warning');
        $this->assertNotNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());
    }
}
