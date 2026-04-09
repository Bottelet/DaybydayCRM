<?php

namespace Tests\Unit\Controllers\InvoiceLine;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
    public function happy_path()
    {
        $this->user->attachRole(Role::whereName('owner')->first());

        $this->assertNotNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());

        $r = $this->json('delete', route('invoiceLine.destroy', $this->invoiceLine->external_id));

        $r->assertStatus(302);
        $this->assertSoftDeleted('invoice_lines', ['external_id' => $this->invoiceLine->external_id]);
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
