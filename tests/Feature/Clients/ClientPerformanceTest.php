<?php

namespace Tests\Feature\Clients;

use App\Enums\PermissionName;
use App\Http\Controllers\ClientsController;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Industry;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(ClientsController::class)]
class ClientPerformanceTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-15 12:00:00');

        Setting::firstOrCreate(
            ['id' => 1],
            [
                'client_number'  => 10000,
                'invoice_number' => 10000,
                'country'        => 'US',
                'company'        => 'Test Company',
                'max_users'      => 10,
                'vat'            => 0,
            ]
        );

        // Flush query log to ensure clean state
        DB::flushQueryLog();
    }

    protected function tearDown(): void
    {
        // Disable query logging and flush to prevent memory leaks
        DB::disableQueryLog();
        DB::flushQueryLog();
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_lists_clients_without_n_plus_1_queries()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_VIEW);
        $this->user = $this->user->fresh();

        $industry = Industry::factory()->create();

        // Create 50 clients to simulate load
        Client::factory()
            ->count(50)
            ->create([
                'industry_id' => $industry->id,
                'user_id'     => $this->user->id,
            ]);

        /* Act & Assert */
        // Flush and enable query logging
        DB::flushQueryLog();
        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $this->actingAs($this->user)->json('GET', route('clients.data'));

        $queryCount = count(DB::getQueryLog());

        /* Assert */
        $response->assertStatus(200);

        // With proper eager loading, should be very few queries:
        // 1. Select clients
        // 2-3. Datatables internal queries
        // Should NOT be 50+ queries (one per client)
        $this->assertLessThan(
            10,
            $queryCount,
            "Expected less than 10 queries but got {$queryCount}. This indicates an N+1 problem."
        );
    }

    #[Test]
    public function it_shows_client_detail_without_n_plus_1_queries()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_VIEW);
        $this->user = $this->user->fresh();

        $industry     = Industry::factory()->create();
        $assignedUser = User::factory()->create();

        $client = Client::factory()->create([
            'industry_id' => $industry->id,
            'user_id'     => $assignedUser->id,
        ]);

        Contact::factory()->create([
            'client_id'  => $client->id,
            'is_primary' => true,
        ]);

        /* Act */
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->user)->get(route('clients.show', $client->external_id));

        $queryCount = count(DB::getQueryLog());

        /* Assert */
        $response->assertStatus(200);

        // With proper eager loading, should be minimal queries:
        // 1. Get client
        // 2. Get related data (invoices, users, etc)
        // Should NOT have dozens of separate queries
        $this->assertLessThan(
            20,
            $queryCount,
            "Expected less than 20 queries but got {$queryCount}. This indicates an N+1 problem in client detail view."
        );
    }

    #[Test]
    public function it_loads_task_datatable_without_n_plus_1_queries()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_VIEW, PermissionName::TASK_VIEW);
        $this->user = $this->user->fresh();

        $industry     = Industry::factory()->create();
        $assignedUser = User::factory()->create();

        $client = Client::factory()->create([
            'industry_id' => $industry->id,
            'user_id'     => $this->user->id,
        ]);

        $taskStatus = Status::factory()->create([
            'source_type' => Task::class,
        ]);

        // Create 20 tasks
        Task::factory()->count(20)->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $assignedUser->id,
            'status_id'        => $taskStatus->id,
        ]);

        /* Act */
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->user)->json('GET', route('clients.taskDataTable', $client->external_id));

        $queryCount = count(DB::getQueryLog());

        /* Assert */
        $response->assertStatus(200);

        // Should have minimal queries with proper eager loading
        // Query count assertion verifies assigned_user relationship is eager loaded to prevent N+1 queries
        $this->assertLessThan(
            10,
            $queryCount,
            "Expected less than 10 queries but got {$queryCount}. This indicates an N+1 problem in task datatable."
        );
    }

    #[Test]
    public function it_loads_project_datatable_without_n_plus_1_queries()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_VIEW, PermissionName::PROJECT_VIEW);
        $this->user = $this->user->fresh();

        $industry     = Industry::factory()->create();
        $assignedUser = User::factory()->create();

        $client = Client::factory()->create([
            'industry_id' => $industry->id,
            'user_id'     => $this->user->id,
        ]);

        $projectStatus = Status::factory()->create([
            'source_type' => Project::class,
        ]);

        // Create 20 projects
        Project::factory()->count(20)->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $assignedUser->id,
            'status_id'        => $projectStatus->id,
        ]);

        /* Act */
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->user)->json('GET', route('clients.projectDataTable', $client->external_id));

        $queryCount = count(DB::getQueryLog());

        /* Assert */
        $response->assertStatus(200);

        // Should have minimal queries with proper eager loading
        // Query count assertion verifies assignee relationship is eager loaded to prevent N+1 queries
        $this->assertLessThan(
            10,
            $queryCount,
            "Expected less than 10 queries but got {$queryCount}. This indicates an N+1 problem in project datatable."
        );
    }

    #[Test]
    public function it_loads_lead_datatable_without_n_plus_1_queries()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_VIEW, PermissionName::LEAD_VIEW);
        $this->user = $this->user->fresh();

        $industry     = Industry::factory()->create();
        $assignedUser = User::factory()->create();

        $client = Client::factory()->create([
            'industry_id' => $industry->id,
            'user_id'     => $this->user->id,
        ]);

        $leadStatus = Status::factory()->create([
            'source_type' => Lead::class,
        ]);

        // Create 20 leads
        Lead::factory()->count(20)->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $assignedUser->id,
            'status_id'        => $leadStatus->id,
        ]);

        /* Act */
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->user)->json('GET', route('clients.leadDataTable', $client->external_id));

        $queryCount = count(DB::getQueryLog());

        /* Assert */
        $response->assertStatus(200);

        // Should have minimal queries with proper eager loading
        // Query count assertion verifies assigned_user relationship is eager loaded to prevent N+1 queries
        $this->assertLessThan(
            10,
            $queryCount,
            "Expected less than 10 queries but got {$queryCount}. This indicates an N+1 problem in lead datatable."
        );
    }

    #[Test]
    public function it_handles_large_client_load_efficiently()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_VIEW);
        $this->user = $this->user->fresh();

        $industry = Industry::factory()->create();

        // Create 100 clients with contacts to simulate realistic load
        $clients = Client::factory()
            ->count(100)
            ->create([
                'industry_id' => $industry->id,
                'user_id'     => $this->user->id,
            ]);

        foreach ($clients as $client) {
            Contact::factory()->create([
                'client_id'  => $client->id,
                'is_primary' => true,
            ]);
        }

        /* Act */
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->user)->json('GET', route('clients.data'));

        $queryCount = count(DB::getQueryLog());

        /* Assert */
        $response->assertStatus(200);

        // Should be very few queries regardless of client count
        $this->assertLessThan(
            10,
            $queryCount,
            "Query count should not scale with number of clients. Got {$queryCount} queries for 100 clients."
        );
    }
}
