<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Project;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Regression tests verifying that alphabetical reorganization of model relationships
 * within PHPStorm region blocks did not remove or alter any relationship methods.
 *
 * Each changed model file in the PR is covered here to guard against accidental
 * method removal during the refactoring.
 */
class ModelRelationshipOrganizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->user   = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_verifies_all_client_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $client = new Client();

        /* Act */
        $hasAppointments   = method_exists($client, 'appointments');
        $hasContacts       = method_exists($client, 'contacts');
        $hasDocuments      = method_exists($client, 'documents');
        $hasInvoices       = method_exists($client, 'invoices');
        $hasLeads          = method_exists($client, 'leads');
        $hasPrimaryContact = method_exists($client, 'primaryContact');
        $hasProjects       = method_exists($client, 'projects');
        $hasTasks          = method_exists($client, 'tasks');
        $hasUser           = method_exists($client, 'user');

        /* Assert */
        $this->assertTrue($hasAppointments, 'appointments() should exist on Clients');
        $this->assertTrue($hasContacts, 'contacts() should exist on Clients');
        $this->assertTrue($hasDocuments, 'documents() should exist on Clients');
        $this->assertTrue($hasInvoices, 'invoices() should exist on Clients');
        $this->assertTrue($hasLeads, 'leads() should exist on Clients');
        $this->assertTrue($hasPrimaryContact, 'primaryContact() should exist on Clients');
        $this->assertTrue($hasProjects, 'projects() should exist on Clients');
        $this->assertTrue($hasTasks, 'tasks() should exist on Clients');
        $this->assertTrue($hasUser, 'user() should exist on Clients');
    }

    #[Test]
    public function it_client_invoices_returns_has_many_relationship()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['client_id' => $this->client->id]);

        /* Act */
        $relationship = $this->client->invoices();
        $invoices     = $this->client->invoices;

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $invoices);
        $this->assertEquals($invoice->id, $invoices->first()->id);
    }

    #[Test]
    public function it_client_user_returns_belongs_to_relationship()
    {
        /* Arrange */
        $client = $this->client->fresh();

        /* Act */
        $relationship = $client->user();
        $relatedUser  = $client->user;

        /* Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertNotNull($relatedUser);
        $this->assertEquals($this->user->id, $relatedUser->id);
    }

    #[Test]
    public function it_verifies_all_invoice_line_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $invoiceLine = new InvoiceLine();

        /* Act */
        $hasInvoice = method_exists($invoiceLine, 'invoice');
        $hasTasks   = method_exists($invoiceLine, 'tasks');

        /* Assert */
        $this->assertTrue($hasInvoice, 'invoice() should exist on InvoiceLine');
        $this->assertTrue($hasTasks, 'tasks() should exist on InvoiceLine');
    }

    #[Test]
    public function it_returns_belongs_to_relationship_for_invoice_line_invoice()
    {
        /* Arrange */
        $invoice     = Invoice::factory()->create(['client_id' => $this->client->id]);
        $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $invoice->id]);

        /* Act */
        $relationship   = $invoiceLine->invoice();
        $relatedInvoice = $invoiceLine->invoice;

        /* Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertNotNull($relatedInvoice);
        $this->assertEquals($invoice->id, $relatedInvoice->id);
    }

    #[Test]
    public function it_verifies_all_lead_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $lead = new Lead();

        /* Act */
        $hasActivity     = method_exists($lead, 'activity');
        $hasAppointments = method_exists($lead, 'appointments');
        $hasClient       = method_exists($lead, 'client');
        $hasComments     = method_exists($lead, 'comments');
        $hasCreator      = method_exists($lead, 'creator');
        $hasDocuments    = method_exists($lead, 'documents');
        $hasInvoice      = method_exists($lead, 'invoice');
        $hasNotes        = method_exists($lead, 'notes');
        $hasOffers       = method_exists($lead, 'offers');
        $hasProjects     = method_exists($lead, 'projects');
        $hasStatus       = method_exists($lead, 'status');
        $hasUser         = method_exists($lead, 'user');

        /* Assert */
        $this->assertTrue($hasActivity, 'activity() should exist on Leads');
        $this->assertTrue($hasAppointments, 'appointments() should exist on Leads');
        $this->assertTrue($hasClient, 'client() should exist on Leads');
        $this->assertTrue($hasComments, 'comments() should exist on Leads');
        $this->assertTrue($hasCreator, 'creator() should exist on Leads');
        $this->assertTrue($hasDocuments, 'documents() should exist on Leads');
        $this->assertTrue($hasInvoice, 'invoice() should exist on Leads');
        $this->assertTrue($hasNotes, 'notes() should exist on Leads');
        $this->assertTrue($hasOffers, 'offers() should exist on Leads');
        $this->assertTrue($hasProjects, 'projects() should exist on Leads');
        $this->assertTrue($hasStatus, 'status() should exist on Leads');
        $this->assertTrue($hasUser, 'user() should exist on Leads');
    }

    #[Test]
    public function it_lead_creator_returns_belongs_to_user()
    {
        /* Arrange */
        $lead = Lead::factory()->create([
            'user_created_id' => $this->user->id,
            'client_id'       => $this->client->id,
        ]);

        /* Act */
        $relationship = $lead->creator();
        $creator      = $lead->creator;

        /* Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertNotNull($creator);
        $this->assertEquals($this->user->id, $creator->id);
    }

    #[Test]
    public function it_lead_comments_returns_morph_many_relationship()
    {
        /* Arrange */
        $lead = Lead::factory()->create(['client_id' => $this->client->id]);

        /* Act */
        $relationship = $lead->comments();

        /* Assert */
        $this->assertInstanceOf(MorphMany::class, $relationship);
    }

    #[Test]
    public function it_lead_notes_is_alias_for_comments()
    {
        /* Arrange */
        $lead = Lead::factory()->create(['client_id' => $this->client->id]);

        /* Act */
        $notesRelationship    = $lead->notes();
        $commentsRelationship = $lead->comments();

        /* Assert */
        $this->assertInstanceOf(MorphMany::class, $notesRelationship);
        $this->assertInstanceOf(MorphMany::class, $commentsRelationship);
    }

    #[Test]
    public function it_verifies_all_offer_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $offer = new Offer();

        /* Act */
        $hasInvoice      = method_exists($offer, 'invoice');
        $hasInvoiceLines = method_exists($offer, 'invoiceLines');
        $hasLead         = method_exists($offer, 'lead');
        $hasLines        = method_exists($offer, 'lines');
        $hasSource       = method_exists($offer, 'source');
        $hasStatus       = method_exists($offer, 'status');

        /* Assert */
        $this->assertTrue($hasInvoice, 'invoice() should exist on Offers');
        $this->assertTrue($hasInvoiceLines, 'invoiceLines() should exist on Offers');
        $this->assertTrue($hasLead, 'lead() should exist on Offers');
        $this->assertTrue($hasLines, 'lines() should exist on Offers');
        $this->assertTrue($hasSource, 'source() should exist on Offers');
        $this->assertTrue($hasStatus, 'status() should exist on Offers');
    }

    #[Test]
    public function it_offer_source_returns_morph_to_relationship()
    {
        /* Arrange */
        $lead  = Lead::factory()->create(['client_id' => $this->client->id]);
        $offer = Offer::factory()->create([
            'source_type' => Lead::class,
            'source_id'   => $lead->id,
        ]);

        /* Act */
        $relationship = $offer->source();

        /* Assert */
        $this->assertInstanceOf(MorphTo::class, $relationship);
    }

    #[Test]
    public function it_offer_lead_delegates_to_source()
    {
        /* Arrange */
        $lead  = Lead::factory()->create(['client_id' => $this->client->id]);
        $offer = Offer::factory()->create([
            'source_type' => Lead::class,
            'source_id'   => $lead->id,
        ]);

        /* Act */
        $leadFromOffer = $offer->lead;

        /* Assert */
        $this->assertNotNull($leadFromOffer);
        $this->assertInstanceOf(Lead::class, $leadFromOffer);
        $this->assertEquals($lead->id, $leadFromOffer->id);
    }

    #[Test]
    public function it_offer_lines_is_alias_for_invoice_lines()
    {
        /* Arrange */
        $offer = new Offer();

        /* Act */
        $linesRelationship        = $offer->lines();
        $invoiceLinesRelationship = $offer->invoiceLines();

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $linesRelationship);
        $this->assertInstanceOf(HasMany::class, $invoiceLinesRelationship);
    }

    #[Test]
    public function it_verifies_all_project_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $project = new Project();

        /* Act */
        $hasActivity  = method_exists($project, 'activity');
        $hasAssignee  = method_exists($project, 'assignee');
        $hasClient    = method_exists($project, 'client');
        $hasComments  = method_exists($project, 'comments');
        $hasCreator   = method_exists($project, 'creator');
        $hasDocuments = method_exists($project, 'documents');
        $hasLead      = method_exists($project, 'lead');
        $hasStatus    = method_exists($project, 'status');
        $hasTasks     = method_exists($project, 'tasks');
        $hasUser      = method_exists($project, 'user');

        /* Assert */
        $this->assertTrue($hasActivity, 'activity() should exist on Projects');
        $this->assertTrue($hasAssignee, 'assignee() should exist on Projects');
        $this->assertTrue($hasClient, 'client() should exist on Projects');
        $this->assertTrue($hasComments, 'comments() should exist on Projects');
        $this->assertTrue($hasCreator, 'creator() should exist on Projects');
        $this->assertTrue($hasDocuments, 'documents() should exist on Projects');
        $this->assertTrue($hasLead, 'lead() should exist on Projects');
        $this->assertTrue($hasStatus, 'status() should exist on Projects');
        $this->assertTrue($hasTasks, 'tasks() should exist on Projects');
        $this->assertTrue($hasUser, 'user() should exist on Projects');
    }

    #[Test]
    public function it_project_assignee_and_user_both_return_belongs_to_same_user()
    {
        /* Arrange */
        $project = Project::factory()->create([
            'user_assigned_id' => $this->user->id,
            'client_id'        => $this->client->id,
        ]);

        /* Act */
        $assignee = $project->assignee;
        $user     = $project->user;

        /* Assert */
        $this->assertNotNull($assignee);
        $this->assertNotNull($user);
        $this->assertEquals($this->user->id, $assignee->id);
        $this->assertEquals($this->user->id, $user->id);
        $this->assertEquals($assignee->id, $user->id);
    }

    #[Test]
    public function it_project_tasks_returns_has_many_relationship()
    {
        /* Arrange */
        $project = Project::factory()->create(['client_id' => $this->client->id]);
        $task    = Task::factory()->create([
            'project_id' => $project->id,
            'client_id'  => $this->client->id,
        ]);

        /* Act */
        $relationship = $project->tasks();
        $tasks        = $project->tasks;

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $tasks);
        $this->assertEquals($task->id, $tasks->first()->id);
    }

    #[Test]
    public function it_verifies_all_role_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $role = new Role();

        /* Act */
        $hasPermissions = method_exists($role, 'permissions');
        $hasUserRole    = method_exists($role, 'userRole');

        /* Assert */
        $this->assertTrue($hasPermissions, 'permissions() should exist on Role');
        $this->assertTrue($hasUserRole, 'userRole() should exist on Role');
    }

    #[Test]
    public function it_role_permissions_returns_belongs_to_many_relationship()
    {
        /* Arrange */
        $role = Role::factory()->create();

        /* Act */
        $relationship = $role->permissions();

        /* Assert */
        $this->assertInstanceOf(BelongsToMany::class, $relationship);
    }

    #[Test]
    public function it_verifies_all_setting_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $setting = new Setting();

        /* Act */
        $hasTasks = method_exists($setting, 'tasks');
        $hasUser  = method_exists($setting, 'user');

        /* Assert */
        $this->assertTrue($hasTasks, 'tasks() should exist on Setting');
        $this->assertTrue($hasUser, 'user() should exist on Setting');
    }

    #[Test]
    public function it_setting_user_returns_belongs_to_relationship()
    {
        /* Arrange */
        $setting = new Setting();

        /* Act */
        $relationship = $setting->user();

        /* Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
    }

    #[Test]
    public function it_verifies_all_status_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $status = new Status();

        /* Act */
        $hasLeads    = method_exists($status, 'leads');
        $hasProjects = method_exists($status, 'projects');
        $hasTasks    = method_exists($status, 'tasks');

        /* Assert */
        $this->assertTrue($hasLeads, 'leads() should exist on Status');
        $this->assertTrue($hasProjects, 'projects() should exist on Status');
        $this->assertTrue($hasTasks, 'tasks() should exist on Status');
    }

    #[Test]
    public function it_status_tasks_returns_has_many_relationship()
    {
        /* Arrange */
        $status = Status::factory()->create(['source_type' => Task::class]);
        $task   = Task::factory()->create([
            'status_id' => $status->id,
            'client_id' => $this->client->id,
        ]);

        /* Act */
        $relationship = $status->tasks();
        $tasks        = $status->tasks;

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $tasks);
    }

    #[Test]
    public function it_status_leads_returns_has_many_relationship()
    {
        /* Arrange */
        $status = Status::factory()->create();

        /* Act */
        $relationship = $status->leads();

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
    }

    #[Test]
    public function it_status_projects_returns_has_many_relationship()
    {
        /* Arrange */
        $status = Status::factory()->create();

        /* Act */
        $relationship = $status->projects();

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
    }

    #[Test]
    public function it_verifies_all_task_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $task = new Task();

        /* Act */
        $hasActivity     = method_exists($task, 'activity');
        $hasAppointments = method_exists($task, 'appointments');
        $hasClient       = method_exists($task, 'client');
        $hasComments     = method_exists($task, 'comments');
        $hasCreator      = method_exists($task, 'creator');
        $hasDocuments    = method_exists($task, 'documents');
        $hasInvoice      = method_exists($task, 'invoice');
        $hasProject      = method_exists($task, 'project');
        $hasStatus       = method_exists($task, 'status');
        $hasUser         = method_exists($task, 'user');

        /* Assert */
        $this->assertTrue($hasActivity, 'activity() should exist on Tasks');
        $this->assertTrue($hasAppointments, 'appointments() should exist on Tasks');
        $this->assertTrue($hasClient, 'client() should exist on Tasks');
        $this->assertTrue($hasComments, 'comments() should exist on Tasks');
        $this->assertTrue($hasCreator, 'creator() should exist on Tasks');
        $this->assertTrue($hasDocuments, 'documents() should exist on Tasks');
        $this->assertTrue($hasInvoice, 'invoice() should exist on Tasks');
        $this->assertTrue($hasProject, 'project() should exist on Tasks');
        $this->assertTrue($hasStatus, 'status() should exist on Tasks');
        $this->assertTrue($hasUser, 'user() should exist on Tasks');
    }

    #[Test]
    public function it_task_comments_returns_morph_many_relationship()
    {
        /* Arrange */
        $task = Task::factory()->create(['client_id' => $this->client->id]);

        /* Act */
        $relationship = $task->comments();

        /* Assert */
        $this->assertInstanceOf(MorphMany::class, $relationship);
    }

    #[Test]
    public function it_task_creator_returns_belongs_to_user()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'client_id'       => $this->client->id,
            'user_created_id' => $this->user->id,
        ]);

        /* Act */
        $relationship = $task->creator();
        $creator      = $task->creator;

        /* Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertNotNull($creator);
        $this->assertEquals($this->user->id, $creator->id);
    }

    #[Test]
    public function it_verifies_all_user_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $user = new User();

        /* Act */
        $hasAbsences     = method_exists($user, 'absences');
        $hasAppointments = method_exists($user, 'appointments');
        $hasClients      = method_exists($user, 'clients');
        $hasDepartment   = method_exists($user, 'department');
        $hasIntegrations = method_exists($user, 'integrations');
        $hasLeads        = method_exists($user, 'leads');
        $hasSettings     = method_exists($user, 'settings');
        $hasTasks        = method_exists($user, 'tasks');
        $hasUserRole     = method_exists($user, 'userRole');

        /* Assert */
        $this->assertTrue($hasAbsences, 'absences() should exist on User');
        $this->assertTrue($hasAppointments, 'appointments() should exist on User');
        $this->assertTrue($hasClients, 'clients() should exist on User');
        $this->assertTrue($hasDepartment, 'department() should exist on User');
        $this->assertTrue($hasIntegrations, 'integrations() should exist on User');
        $this->assertTrue($hasLeads, 'leads() should exist on User');
        $this->assertTrue($hasSettings, 'settings() should exist on User');
        $this->assertTrue($hasTasks, 'tasks() should exist on User');
        $this->assertTrue($hasUserRole, 'userRole() should exist on User');
    }

    #[Test]
    public function it_returns_tasks_as_has_many_relationship_on_user()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_assigned_id' => $this->user->id,
            'client_id'        => $this->client->id,
        ]);

        /* Act */
        $relationship = $this->user->tasks();
        $tasks        = $this->user->tasks;

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $tasks);
        $this->assertEquals($task->id, $tasks->first()->id);
    }

    #[Test]
    public function it_returns_leads_as_has_many_relationship_on_user()
    {
        /* Arrange */
        $lead = Lead::factory()->create([
            'user_assigned_id' => $this->user->id,
            'client_id'        => $this->client->id,
        ]);

        /* Act */
        $relationship = $this->user->leads();
        $leads        = $this->user->leads;

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $leads);
        $this->assertEquals($lead->id, $leads->first()->id);
    }

    #[Test]
    public function it_verifies_all_permission_role_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $permissionRole = new PermissionRole();

        /* Act */
        $hasEmployee = method_exists($permissionRole, 'employee');
        $hasHasperm  = method_exists($permissionRole, 'hasperm');
        $hasSettings = method_exists($permissionRole, 'settings');

        /* Assert */
        $this->assertTrue($hasEmployee, 'employee() should exist on PermissionRole');
        $this->assertTrue($hasHasperm, 'hasperm() should exist on PermissionRole');
        $this->assertTrue($hasSettings, 'settings() should exist on PermissionRole');
    }

    #[Test]
    public function it_verifies_all_permission_relationship_methods_exist_after_reorganization()
    {
        /* Arrange */
        $permission = new Permission();

        /* Act */
        $hasRoles = method_exists($permission, 'roles');

        /* Assert */
        $this->assertTrue($hasRoles, 'roles() should exist on Permission');
    }

    #[Test]
    public function it_permission_roles_returns_belongs_to_many_relationship()
    {
        /* Arrange */
        $permission = Permission::factory()->create();

        /* Act */
        $relationship = $permission->roles();

        /* Assert */
        $this->assertInstanceOf(BelongsToMany::class, $relationship);
    }
}
