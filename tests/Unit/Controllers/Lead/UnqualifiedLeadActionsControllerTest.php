<?php
namespace Tests\Unit\Controllers\Lead;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Lead;
use App\Models\User;
use Ramsey\Uuid\Uuid;
use App\Models\Client;
use App\Models\Status;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Industry;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UnqualifiedLeadActionsControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $client;

    public function setUp(): void
    {
        parent::setUp();

        
        $this->user = factory(User::class)->create();
        $this->client = factory(Client::class)->create();
    }

    /** @test **/
    public function can_convert_to_order()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $lead = factory(Lead::class)->create();

        $this->assertNull($lead->refresh()->invoice);

        $this->json('POST', route('lead.convert.order', $lead->external_id));
        
        $this->assertNotNull($lead->refresh()->invoice);
    }

    /** @test **/
    public function can_convert_to_qualified()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $lead = factory(Lead::class)->create([
            'qualified' => false,
        ]);

        $this->assertEquals($lead->refresh()->qualified, 0);

        $this->json('POST', route('lead.convert.qualified', $lead->external_id));
        
        $this->assertEquals($lead->refresh()->qualified, 1);
    }
}
