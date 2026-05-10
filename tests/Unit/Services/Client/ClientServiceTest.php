<?php

namespace Tests\Unit\Services\Client;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Industry;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Services\Client\ClientService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(ClientService::class)]
class ClientServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private ClientService $clientService;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-15 12:00:00');
        
        $this->clientService = new ClientService();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_gets_clients_for_datatable()
    {
        /* Arrange */
        $industry = Industry::factory()->create();
        $user = User::factory()->create();
        
        Client::factory()->count(3)->create([
            'industry_id' => $industry->id,
            'user_id' => $user->id,
        ]);

        /* Act */
        $query = $this->clientService->getClientsForDataTable();
        $clients = $query->get();

        /* Assert */
        $this->assertCount(3, $clients);
        
        // Should only select specific columns for performance
        $firstClient = $clients->first();
        $this->assertNotNull($firstClient->external_id);
        $this->assertNotNull($firstClient->company_name);
        $this->assertNotNull($firstClient->vat);
        $this->assertNotNull($firstClient->address);
    }

    #[Test]
    public function it_gets_client_with_relations()
    {
        /* Arrange */
        $industry = Industry::factory()->create();
        $user = User::factory()->create();
        
        $client = Client::factory()->create([
            'industry_id' => $industry->id,
            'user_id' => $user->id,
        ]);
        
        Contact::factory()->create([
            'client_id' => $client->id,
            'is_primary' => true,
        ]);

        /* Act */
        $result = $this->clientService->getClientWithRelations($client->external_id);

        /* Assert */
        $this->assertInstanceOf(Client::class, $result);
        $this->assertEquals($client->id, $result->id);
        
        // Verify relationships are eager loaded
        $this->assertTrue($result->relationLoaded('user'));
        $this->assertTrue($result->relationLoaded('primaryContact'));
        $this->assertTrue($result->relationLoaded('industry'));
        $this->assertTrue($result->relationLoaded('documents'));
        $this->assertTrue($result->relationLoaded('appointments'));
    }

    #[Test]
    public function it_gets_tasks_with_relations()
    {
        /* Arrange */
        $client = Client::factory()->create();
        $user = User::factory()->create();
        $status = Status::factory()->create([
            'source_type' => Task::class,
        ]);
        
        Task::factory()->count(3)->create([
            'client_id' => $client->id,
            'user_assigned_id' => $user->id,
            'status_id' => $status->id,
        ]);

        /* Act */
        $tasks = $this->clientService->getTasksWithRelations($client);

        /* Assert */
        $this->assertCount(3, $tasks);
        
        // Verify relationships are eager loaded
        $firstTask = $tasks->first();
        $this->assertTrue($firstTask->relationLoaded('status'));
        $this->assertTrue($firstTask->relationLoaded('user'));
        
        // Verify we can access the relationship without additional queries
        $this->assertEquals($status->id, $firstTask->status->id);
        $this->assertEquals($user->id, $firstTask->user->id);
    }

    #[Test]
    public function it_gets_projects_with_relations()
    {
        /* Arrange */
        $client = Client::factory()->create();
        $user = User::factory()->create();
        $status = Status::factory()->create([
            'source_type' => Project::class,
        ]);
        
        Project::factory()->count(3)->create([
            'client_id' => $client->id,
            'user_assigned_id' => $user->id,
            'status_id' => $status->id,
        ]);

        /* Act */
        $projects = $this->clientService->getProjectsWithRelations($client);

        /* Assert */
        $this->assertCount(3, $projects);
        
        // Verify relationships are eager loaded
        $firstProject = $projects->first();
        $this->assertTrue($firstProject->relationLoaded('status'));
        $this->assertTrue($firstProject->relationLoaded('assignee'));
        
        // Verify we can access the relationship without additional queries
        $this->assertEquals($status->id, $firstProject->status->id);
        $this->assertEquals($user->id, $firstProject->assignee->id);
    }

    #[Test]
    public function it_gets_leads_with_relations()
    {
        /* Arrange */
        $client = Client::factory()->create();
        $user = User::factory()->create();
        $status = Status::factory()->create([
            'source_type' => Lead::class,
        ]);
        
        Lead::factory()->count(3)->create([
            'client_id' => $client->id,
            'user_assigned_id' => $user->id,
            'status_id' => $status->id,
        ]);

        /* Act */
        $leads = $this->clientService->getLeadsWithRelations($client);

        /* Assert */
        $this->assertCount(3, $leads);
        
        // Verify relationships are eager loaded
        $firstLead = $leads->first();
        $this->assertTrue($firstLead->relationLoaded('status'));
        $this->assertTrue($firstLead->relationLoaded('user'));
        
        // Verify we can access the relationship without additional queries
        $this->assertEquals($status->id, $firstLead->status->id);
        $this->assertEquals($user->id, $firstLead->user->id);
    }

    #[Test]
    public function it_gets_invoices_with_relations()
    {
        /* Arrange */
        $client = Client::factory()->create();
        
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
        ]);
        
        InvoiceLine::factory()->count(2)->create([
            'invoice_id' => $invoice->id,
        ]);

        /* Act */
        $invoices = $this->clientService->getInvoicesWithRelations($client);

        /* Assert */
        $this->assertCount(1, $invoices);
        
        // Verify relationships are eager loaded
        $firstInvoice = $invoices->first();
        $this->assertTrue($firstInvoice->relationLoaded('invoiceLines'));
        
        // Verify we can access the relationship without additional queries
        $this->assertCount(2, $firstInvoice->invoiceLines);
    }

    #[Test]
    public function it_gets_all_invoices_for_client()
    {
        /* Arrange */
        $client = Client::factory()->create();
        
        Invoice::factory()->count(3)->create([
            'client_id' => $client->id,
        ]);

        /* Act */
        $invoices = $this->clientService->getInvoices($client);

        /* Assert */
        $this->assertCount(3, $invoices);
        
        // Verify relationships are eager loaded
        $firstInvoice = $invoices->first();
        $this->assertTrue($firstInvoice->relationLoaded('invoiceLines'));
    }

    #[Test]
    public function it_finds_client_by_external_id()
    {
        /* Arrange */
        $client = Client::factory()->create();

        /* Act */
        $result = $this->clientService->findByExternalId($client->external_id);

        /* Assert */
        $this->assertInstanceOf(Client::class, $result);
        $this->assertEquals($client->id, $result->id);
    }

    #[Test]
    public function it_throws_exception_when_client_not_found()
    {
        /* Arrange */
        $nonExistentId = 'non-existent-uuid';

        /* Act & Assert */
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->clientService->findByExternalId($nonExistentId);
    }
}
