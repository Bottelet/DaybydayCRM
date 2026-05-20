<?php

namespace Tests\Unit\Clients;

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
        $user     = User::factory()->create();

        Client::factory()->count(3)->create([
            'industry_id' => $industry->id,
            'user_id'     => $user->id,
        ]);

        /* Act */
        $query   = $this->clientService->getClientsForDataTable();
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
        $user     = User::factory()->create();

        $client = Client::factory()->create([
            'industry_id' => $industry->id,
            'user_id'     => $user->id,
        ]);

        Contact::factory()->create([
            'client_id'  => $client->id,
            'is_primary' => true,
        ]);

        /* Act */
        $result = $this->clientService->getClientWithRelations($client->external_id);

        /* Assert */
        $this->assertInstanceOf(Client::class, $result);
        $this->assertEquals($client->id, $result->id);
        $this->assertEquals($client->company_name, $result->company_name);
        $this->assertEquals($client->external_id, $result->external_id);

        // Verify relationships are eager loaded
        $this->assertTrue($result->relationLoaded('user'));
        $this->assertTrue($result->relationLoaded('primaryContact'));
        $this->assertTrue($result->relationLoaded('industry'));
        $this->assertTrue($result->relationLoaded('documents'));
        $this->assertTrue($result->relationLoaded('appointments'));

        // Verify relationship data matches
        $this->assertEquals($user->id, $result->user->id);
        $this->assertEquals($industry->id, $result->industry->id);
    }

    #[Test]
    public function it_gets_tasks_with_relations()
    {
        /* Arrange */
        $client = Client::factory()->create();
        $user   = User::factory()->create();
        $status = Status::factory()->create([
            'source_type' => Task::class,
        ]);

        $createdTasks = Task::factory()->count(3)->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $user->id,
            'status_id'        => $status->id,
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

        // Verify all tasks belong to the client
        foreach ($tasks as $task) {
            $this->assertEquals($client->id, $task->client_id);
        }
    }

    #[Test]
    public function it_gets_projects_with_relations()
    {
        /* Arrange */
        $client = Client::factory()->create();
        $user   = User::factory()->create();
        $status = Status::factory()->create([
            'source_type' => Project::class,
        ]);

        $createdProjects = Project::factory()->count(3)->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $user->id,
            'status_id'        => $status->id,
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

        // Verify all projects belong to the client
        foreach ($projects as $project) {
            $this->assertEquals($client->id, $project->client_id);
        }
    }

    #[Test]
    public function it_gets_leads_with_relations()
    {
        /* Arrange */
        $client = Client::factory()->create();
        $user   = User::factory()->create();
        $status = Status::factory()->create([
            'source_type' => Lead::class,
        ]);

        $createdLeads = Lead::factory()->count(3)->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $user->id,
            'status_id'        => $status->id,
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

        // Verify all leads belong to the client
        foreach ($leads as $lead) {
            $this->assertEquals($client->id, $lead->client_id);
        }
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
        $invoicesQuery = $this->clientService->getInvoicesWithRelations($client);

        /* Assert */
        // Should return the invoices relation query, not a materialized Collection
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $invoicesQuery);
        // Execute the query to verify eager loading works
        $invoices = $invoicesQuery->get();
        $this->assertCount(1, $invoices);

        // Verify relationships are eager loaded
        $firstInvoice = $invoices->first();
        $this->assertTrue($firstInvoice->relationLoaded('invoiceLines'));

        // Verify we can access the relationship without additional queries
        $this->assertCount(2, $firstInvoice->invoiceLines);

        // Verify invoice belongs to the client
        $this->assertEquals($client->id, $firstInvoice->client_id);
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

    #[Test]
    public function it_creates_client_with_contact()
    {
        /* Arrange */
        $industry = Industry::factory()->create();
        $user     = User::factory()->create();

        $data = [
            'name'             => 'John Doe',
            'company_name'     => 'Test Company',
            'vat'              => '12345678',
            'email'            => 'john@example.com',
            'address'          => '123 Main St',
            'zipcode'          => '12345',
            'city'             => 'Springfield',
            'primary_number'   => '1234567890',
            'secondary_number' => '0987654321',
            'industry_id'      => $industry->id,
            'company_type'     => 'LLC',
            'user_id'          => $user->id,
        ];

        /* Act */
        [$client, $contact] = $this->clientService->createClientWithContact($data);

        /* Assert */
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(Contact::class, $contact);

        // Verify client data
        $this->assertEquals($data['company_name'], $client->company_name);
        $this->assertEquals($data['vat'], $client->vat);
        $this->assertEquals($data['address'], $client->address);
        $this->assertEquals($data['zipcode'], $client->zipcode);
        $this->assertEquals($data['city'], $client->city);
        $this->assertEquals($data['company_type'], $client->company_type);
        $this->assertEquals($industry->id, $client->industry_id);
        $this->assertEquals($user->id, $client->user_id);
        $this->assertNotNull($client->external_id);

        // Verify contact data
        $this->assertEquals($data['name'], $contact->name);
        $this->assertEquals($data['email'], $contact->email);
        $this->assertEquals($data['primary_number'], $contact->primary_number);
        $this->assertEquals($data['secondary_number'], $contact->secondary_number);
        $this->assertTrue($contact->is_primary);
        $this->assertEquals($client->id, $contact->client_id);
        $this->assertNotNull($contact->external_id);
    }

    #[Test]
    public function it_creates_client_with_contact_with_minimal_data()
    {
        /* Arrange */
        $industry = Industry::factory()->create();
        $user     = User::factory()->create();

        $data = [
            'name'         => 'Jane Doe',
            'company_name' => 'Minimal Co',
            'email'        => 'jane@example.com',
            'industry_id'  => $industry->id,
            'user_id'      => $user->id,
        ];

        /* Act */
        [$client, $contact] = $this->clientService->createClientWithContact($data);

        /* Assert */
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(Contact::class, $contact);

        // Verify required fields are set
        $this->assertEquals($data['company_name'], $client->company_name);
        $this->assertEquals($data['name'], $contact->name);
        $this->assertEquals($data['email'], $contact->email);

        // Verify optional fields are null
        $this->assertNull($client->vat);
        $this->assertNull($client->address);
        $this->assertNull($client->zipcode);
        $this->assertNull($client->city);
        $this->assertNull($contact->primary_number);
        $this->assertNull($contact->secondary_number);
    }

    #[Test]
    public function it_creates_client_and_contact_in_database()
    {
        /* Arrange */
        $industry = Industry::factory()->create();
        $user     = User::factory()->create();

        $data = [
            'name'             => 'Test Contact',
            'company_name'     => 'Test Corp',
            'vat'              => '98765432',
            'email'            => 'test@example.com',
            'address'          => '456 Oak Ave',
            'zipcode'          => '54321',
            'city'             => 'Shelbyville',
            'primary_number'   => '5555555555',
            'secondary_number' => '4444444444',
            'industry_id'      => $industry->id,
            'company_type'     => 'S-Corp',
            'user_id'          => $user->id,
        ];

        /* Act */
        [$client, $contact] = $this->clientService->createClientWithContact($data);

        /* Assert */
        // Verify client exists in database
        $this->assertDatabaseHas('clients', [
            'company_name' => 'Test Corp',
            'vat'          => '98765432',
            'industry_id'  => $industry->id,
            'user_id'      => $user->id,
        ]);

        // Verify contact exists in database
        $this->assertDatabaseHas('contacts', [
            'name'       => 'Test Contact',
            'email'      => 'test@example.com',
            'client_id'  => $client->id,
            'is_primary' => true,
        ]);

        // Fetch fresh and verify relationships
        $freshClient  = Client::findOrFail($client->id);
        $freshContact = $freshClient->primaryContact;

        $this->assertNotNull($freshContact);
        $this->assertEquals('Test Contact', $freshContact->name);
        $this->assertTrue($freshContact->is_primary);
    }

    #[Test]
    public function it_marks_contact_as_primary()
    {
        /* Arrange */
        $industry = Industry::factory()->create();
        $user     = User::factory()->create();

        $data = [
            'name'         => 'Primary Contact',
            'company_name' => 'Primary Test Co',
            'email'        => 'primary@example.com',
            'industry_id'  => $industry->id,
            'user_id'      => $user->id,
        ];

        /* Act */
        [$client, $contact] = $this->clientService->createClientWithContact($data);

        /* Assert */
        $this->assertTrue($contact->is_primary);
        $this->assertEquals($contact->id, $client->primaryContact->id);
    }
}
