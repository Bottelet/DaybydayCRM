<?php

namespace Tests\Feature\Leads;

use App\Enums\PermissionName;
use App\Http\Controllers\LeadsController;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(LeadsController::class)]
class LeadsIndexAndShowTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        $client     = Client::factory()->create();
        $this->lead = Lead::factory()->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $this->user->id,
            'status_id'        => Status::factory()->create(['source_type' => Lead::class])->id,
        ]);
    }

    #[Test]
    public function it_can_view_leads_index()
    {
        $response = $this->get(route('leads.index'));

        $response->assertStatus(200);
        $response->assertViewIs('leads.index');
    }

    #[Test]
    public function it_can_view_lead_show_page()
    {
        $response = $this->get(route('leads.show', $this->lead->external_id));

        $response->assertStatus(200);
        $response->assertViewIs('leads.show');
    }

    #[Test]
    public function it_returns_404_for_nonexistent_lead()
    {
        $response = $this->get(route('leads.show', 'nonexistent-external-id'));

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_leads_json_datatables_response()
    {
        $response = $this->get(route('leads.data'), ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    }

    #[Test]
    public function it_can_delete_lead_via_json()
    {
        $this->withPermissions(PermissionName::LEAD_DELETE);

        $response = $this->delete(route('leads.destroy.json', $this->lead), [], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $this->assertSoftDeleted('leads', ['id' => $this->lead->id]);
    }
}
