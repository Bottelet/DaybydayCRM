<?php
namespace Tests\Unit\Controllers\InvoiceLine;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Project;
use App\Models\Status;
use App\Models\Lead;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\User;
use App\Models\Industry;
use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InvoiceLinesControllerTest extends TestCase
{
    use DatabaseTransactions;


    private $invoice;
    private $invoiceLine;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice = factory(Invoice::class)->create();
        $this->invoiceLine = factory(InvoiceLine::class)->create([
            'invoice_id' => $this->invoice->id
        ]);
    }

    /** @test **/
    public function happyPath()
    {
        $this->assertNotNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());

        $r = $this->json('delete', route('invoiceLine.destroy', $this->invoiceLine->external_id));

        $r->assertStatus(302);
        $this->assertNull(InvoiceLine::where('external_id', $this->invoiceLine->external_id)->first());
    }


    /** @test **/
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
