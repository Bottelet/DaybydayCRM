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
use App\Models\RoleUser;
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

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region Client relationships

    #[Test]
    public function it_client_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $client = new Client();

        /** Act & Assert */
        $this->assertTrue(method_exists($client, 'appointments'), 'appointments() should exist on Client');
        $this->assertTrue(method_exists($client, 'contacts'), 'contacts() should exist on Client');
        $this->assertTrue(method_exists($client, 'documents'), 'documents() should exist on Client');
        $this->assertTrue(method_exists($client, 'invoices'), 'invoices() should exist on Client');
        $this->assertTrue(method_exists($client, 'leads'), 'leads() should exist on Client');
        $this->assertTrue(method_exists($client, 'primaryContact'), 'primaryContact() should exist on Client');
        $this->assertTrue(method_exists($client, 'projects'), 'projects() should exist on Client');
        $this->assertTrue(method_exists($client, 'tasks'), 'tasks() should exist on Client');
        $this->assertTrue(method_exists($client, 'user'), 'user() should exist on Client');
    }

    #[Test]
    public function it_client_invoices_returns_has_many_relationship()
    {
        /** Arrange */
        $invoice = Invoice::factory()->create(['client_id' => $this->client->id]);

        /** Act */
        $relationship = $this->client->invoices();
        $invoices = $this->client->invoices;

        /** Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $invoices);
        $this->assertEquals($invoice->id, $invoices->first()->id);
    }

    #[Test]
    public function it_client_user_returns_belongs_to_relationship()
    {
        /** Arrange */
        $client = $this->client->fresh();

        /** Act */
        $relationship = $client->user();
        $relatedUser = $client->user;

        /** Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertNotNull($relatedUser);
        $this->assertEquals($this->user->id, $relatedUser->id);
    }

    // endregion

    // region InvoiceLine relationships

    #[Test]
    public function it_invoice_line_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $invoiceLine = new InvoiceLine();

        /** Act & Assert */
        $this->assertTrue(method_exists($invoiceLine, 'invoice'), 'invoice() should exist on InvoiceLine');
        $this->assertTrue(method_exists($invoiceLine, 'tasks'), 'tasks() should exist on InvoiceLine');
    }

    #[Test]
    public function it_invoice_line_invoice_returns_belongs_to_relationship()
    {
        /** Arrange */
        $invoice = Invoice::factory()->create(['client_id' => $this->client->id]);
        $invoiceLine = InvoiceLine::factory()->create(['invoice_id' => $invoice->id]);

        /** Act */
        $relationship = $invoiceLine->invoice();
        $relatedInvoice = $invoiceLine->invoice;

        /** Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertNotNull($relatedInvoice);
        $this->assertEquals($invoice->id, $relatedInvoice->id);
    }

    // endregion

    // region Lead relationships

    #[Test]
    public function it_lead_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $lead = new Lead();

        /** Act & Assert */
        $this->assertTrue(method_exists($lead, 'activity'), 'activity() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'appointments'), 'appointments() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'client'), 'client() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'comments'), 'comments() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'creator'), 'creator() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'documents'), 'documents() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'invoice'), 'invoice() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'notes'), 'notes() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'offers'), 'offers() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'projects'), 'projects() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'status'), 'status() should exist on Lead');
        $this->assertTrue(method_exists($lead, 'user'), 'user() should exist on Lead');
    }

    #[Test]
    public function it_lead_creator_returns_belongs_to_user()
    {
        /** Arrange */
        $lead = Lead::factory()->create([
            'user_created_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        /** Act */
        $relationship = $lead->creator();
        $creator = $lead->creator;

        /** Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertNotNull($creator);
        $this->assertEquals($this->user->id, $creator->id);
    }

    #[Test]
    public function it_lead_comments_returns_morph_many_relationship()
    {
        /** Arrange */
        $lead = Lead::factory()->create(['client_id' => $this->client->id]);

        /** Act */
        $relationship = $lead->comments();

        /** Assert */
        $this->assertInstanceOf(MorphMany::class, $relationship);
    }

    #[Test]
    public function it_lead_notes_is_alias_for_comments()
    {
        /** Arrange */
        $lead = Lead::factory()->create(['client_id' => $this->client->id]);

        /** Act */
        $notesRelationship = $lead->notes();
        $commentsRelationship = $lead->comments();

        /** Assert */
        $this->assertInstanceOf(MorphMany::class, $notesRelationship);
        $this->assertInstanceOf(MorphMany::class, $commentsRelationship);
    }

    // endregion

    // region Offer relationships

    #[Test]
    public function it_offer_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $offer = new Offer();

        /** Act & Assert */
        $this->assertTrue(method_exists($offer, 'invoice'), 'invoice() should exist on Offer');
        $this->assertTrue(method_exists($offer, 'invoiceLines'), 'invoiceLines() should exist on Offer');
        $this->assertTrue(method_exists($offer, 'lead'), 'lead() should exist on Offer');
        $this->assertTrue(method_exists($offer, 'lines'), 'lines() should exist on Offer');
        $this->assertTrue(method_exists($offer, 'source'), 'source() should exist on Offer');
        $this->assertTrue(method_exists($offer, 'status'), 'status() should exist on Offer');
    }

    #[Test]
    public function it_offer_source_returns_morph_to_relationship()
    {
        /** Arrange */
        $lead = Lead::factory()->create(['client_id' => $this->client->id]);
        $offer = Offer::factory()->create([
            'source_type' => Lead::class,
            'source_id' => $lead->id,
        ]);

        /** Act */
        $relationship = $offer->source();

        /** Assert */
        $this->assertInstanceOf(MorphTo::class, $relationship);
    }

    #[Test]
    public function it_offer_lead_delegates_to_source()
    {
        /** Arrange */
        $lead = Lead::factory()->create(['client_id' => $this->client->id]);
        $offer = Offer::factory()->create([
            'source_type' => Lead::class,
            'source_id' => $lead->id,
        ]);

        /** Act */
        $leadFromOffer = $offer->lead;

        /** Assert */
        $this->assertNotNull($leadFromOffer);
        $this->assertInstanceOf(Lead::class, $leadFromOffer);
        $this->assertEquals($lead->id, $leadFromOffer->id);
    }

    #[Test]
    public function it_offer_lines_is_alias_for_invoice_lines()
    {
        /** Arrange */
        $offer = new Offer();

        /** Act */
        $linesRelationship = $offer->lines();
        $invoiceLinesRelationship = $offer->invoiceLines();

        /** Assert */
        $this->assertInstanceOf(HasMany::class, $linesRelationship);
        $this->assertInstanceOf(HasMany::class, $invoiceLinesRelationship);
    }

    // endregion

    // region Project relationships

    #[Test]
    public function it_project_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $project = new Project();

        /** Act & Assert */
        $this->assertTrue(method_exists($project, 'activity'), 'activity() should exist on Project');
        $this->assertTrue(method_exists($project, 'assignee'), 'assignee() should exist on Project');
        $this->assertTrue(method_exists($project, 'client'), 'client() should exist on Project');
        $this->assertTrue(method_exists($project, 'comments'), 'comments() should exist on Project');
        $this->assertTrue(method_exists($project, 'creator'), 'creator() should exist on Project');
        $this->assertTrue(method_exists($project, 'documents'), 'documents() should exist on Project');
        $this->assertTrue(method_exists($project, 'lead'), 'lead() should exist on Project');
        $this->assertTrue(method_exists($project, 'status'), 'status() should exist on Project');
        $this->assertTrue(method_exists($project, 'tasks'), 'tasks() should exist on Project');
        $this->assertTrue(method_exists($project, 'user'), 'user() should exist on Project');
    }

    #[Test]
    public function it_project_assignee_and_user_both_return_belongs_to_same_user()
    {
        /** Arrange */
        $project = Project::factory()->create([
            'user_assigned_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        /** Act */
        $assignee = $project->assignee;
        $user = $project->user;

        /** Assert */
        $this->assertNotNull($assignee);
        $this->assertNotNull($user);
        $this->assertEquals($this->user->id, $assignee->id);
        $this->assertEquals($this->user->id, $user->id);
        $this->assertEquals($assignee->id, $user->id);
    }

    #[Test]
    public function it_project_tasks_returns_has_many_relationship()
    {
        /** Arrange */
        $project = Project::factory()->create(['client_id' => $this->client->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'client_id' => $this->client->id,
        ]);

        /** Act */
        $relationship = $project->tasks();
        $tasks = $project->tasks;

        /** Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $tasks);
        $this->assertEquals($task->id, $tasks->first()->id);
    }

    // endregion

    // region Role relationships

    #[Test]
    public function it_role_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $role = new Role();

        /** Act & Assert */
        $this->assertTrue(method_exists($role, 'permissions'), 'permissions() should exist on Role');
        $this->assertTrue(method_exists($role, 'userRole'), 'userRole() should exist on Role');
    }

    #[Test]
    public function it_role_permissions_returns_belongs_to_many_relationship()
    {
        /** Arrange */
        $role = Role::factory()->create();

        /** Act */
        $relationship = $role->permissions();

        /** Assert */
        $this->assertInstanceOf(BelongsToMany::class, $relationship);
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\Group('regression')]
    public function it_role_user_role_returns_has_many_of_role_user_not_role()
    {
        /** Arrange */
        // Before the fix: userRole() returned hasMany(Role::class, 'user_id', 'id') - wrong model & wrong FK
        // After the fix: userRole() returns hasMany(RoleUser::class, 'role_id', 'id') - correct
        $role = Role::factory()->create();

        /** Act */
        $relationship = $role->userRole();

        /** Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertEquals(
            (new RoleUser())->getTable(),
            $relationship->getRelated()->getTable(),
            'userRole() should relate to the role_user table, not the roles table'
        );
    }

    // endregion

    // region Setting relationships

    #[Test]
    public function it_setting_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $setting = new Setting();

        /** Act & Assert */
        // Note: tasks() was removed from Setting in this PR (it was an incorrect belongsTo Task relationship)
        $this->assertTrue(method_exists($setting, 'user'), 'user() should exist on Setting');
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\Group('regression')]
    public function it_setting_does_not_have_tasks_relationship_after_removal()
    {
        /** Arrange */
        // The tasks() belongsTo relationship was removed from Setting because it was incorrect.
        // Setting should not have a direct Task relationship.
        $setting = new Setting();

        /** Act & Assert */
        $this->assertFalse(
            method_exists($setting, 'tasks'),
            'tasks() should NOT exist on Setting - it was removed in this PR as it was an incorrect relationship'
        );
    }

    #[Test]
    public function it_setting_user_returns_belongs_to_relationship()
    {
        /** Arrange */
        $setting = new Setting();

        /** Act */
        $relationship = $setting->user();

        /** Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
    }

    // endregion

    // region Status relationships

    #[Test]
    public function it_status_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $status = new Status();

        /** Act & Assert */
        $this->assertTrue(method_exists($status, 'leads'), 'leads() should exist on Status');
        $this->assertTrue(method_exists($status, 'projects'), 'projects() should exist on Status');
        $this->assertTrue(method_exists($status, 'tasks'), 'tasks() should exist on Status');
    }

    #[Test]
    public function it_status_tasks_returns_has_many_relationship()
    {
        /** Arrange */
        $status = Status::factory()->create(['source_type' => Task::class]);
        $task = Task::factory()->create([
            'status_id' => $status->id,
            'client_id' => $this->client->id,
        ]);

        /** Act */
        $relationship = $status->tasks();
        $tasks = $status->tasks;

        /** Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $tasks);
    }

    #[Test]
    public function it_status_leads_returns_has_many_relationship()
    {
        /** Arrange */
        $status = Status::factory()->create();

        /** Act */
        $relationship = $status->leads();

        /** Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
    }

    #[Test]
    public function it_status_projects_returns_has_many_relationship()
    {
        /** Arrange */
        $status = Status::factory()->create();

        /** Act */
        $relationship = $status->projects();

        /** Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
    }

    // endregion

    // region Task relationships

    #[Test]
    public function it_task_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $task = new Task();

        /** Act & Assert */
        $this->assertTrue(method_exists($task, 'activity'), 'activity() should exist on Task');
        $this->assertTrue(method_exists($task, 'appointments'), 'appointments() should exist on Task');
        $this->assertTrue(method_exists($task, 'client'), 'client() should exist on Task');
        $this->assertTrue(method_exists($task, 'comments'), 'comments() should exist on Task');
        $this->assertTrue(method_exists($task, 'creator'), 'creator() should exist on Task');
        $this->assertTrue(method_exists($task, 'documents'), 'documents() should exist on Task');
        $this->assertTrue(method_exists($task, 'invoice'), 'invoice() should exist on Task');
        $this->assertTrue(method_exists($task, 'project'), 'project() should exist on Task');
        $this->assertTrue(method_exists($task, 'status'), 'status() should exist on Task');
        $this->assertTrue(method_exists($task, 'user'), 'user() should exist on Task');
    }

    #[Test]
    public function it_task_comments_returns_morph_many_relationship()
    {
        /** Arrange */
        $task = Task::factory()->create(['client_id' => $this->client->id]);

        /** Act */
        $relationship = $task->comments();

        /** Assert */
        $this->assertInstanceOf(MorphMany::class, $relationship);
    }

    #[Test]
    public function it_task_creator_returns_belongs_to_user()
    {
        /** Arrange */
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'user_created_id' => $this->user->id,
        ]);

        /** Act */
        $relationship = $task->creator();
        $creator = $task->creator;

        /** Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertNotNull($creator);
        $this->assertEquals($this->user->id, $creator->id);
    }

    // endregion

    // region User relationships

    #[Test]
    public function it_user_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $user = new User();

        /** Act & Assert */
        $this->assertTrue(method_exists($user, 'absences'), 'absences() should exist on User');
        $this->assertTrue(method_exists($user, 'appointments'), 'appointments() should exist on User');
        $this->assertTrue(method_exists($user, 'clients'), 'clients() should exist on User');
        $this->assertTrue(method_exists($user, 'department'), 'department() should exist on User');
        $this->assertTrue(method_exists($user, 'integrations'), 'integrations() should exist on User');
        $this->assertTrue(method_exists($user, 'leads'), 'leads() should exist on User');
        $this->assertTrue(method_exists($user, 'settings'), 'settings() should exist on User');
        $this->assertTrue(method_exists($user, 'tasks'), 'tasks() should exist on User');
        $this->assertTrue(method_exists($user, 'userRole'), 'userRole() should exist on User');
    }

    #[Test]
    public function it_user_tasks_returns_has_many_relationship()
    {
        /** Arrange */
        $task = Task::factory()->create([
            'user_assigned_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        /** Act */
        $relationship = $this->user->tasks();
        $tasks = $this->user->tasks;

        /** Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $tasks);
        $this->assertEquals($task->id, $tasks->first()->id);
    }

    #[Test]
    public function it_user_leads_returns_has_many_relationship()
    {
        /** Arrange */
        $lead = Lead::factory()->create([
            'user_assigned_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        /** Act */
        $relationship = $this->user->leads();
        $leads = $this->user->leads;

        /** Assert */
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertCount(1, $leads);
        $this->assertEquals($lead->id, $leads->first()->id);
    }

    // endregion

    // region PermissionRole relationships

    #[Test]
    public function it_permission_role_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $permissionRole = new PermissionRole();

        /** Act & Assert */
        $this->assertTrue(method_exists($permissionRole, 'employee'), 'employee() should exist on PermissionRole');
        $this->assertTrue(method_exists($permissionRole, 'hasperm'), 'hasperm() should exist on PermissionRole');
        $this->assertTrue(method_exists($permissionRole, 'settings'), 'settings() should exist on PermissionRole');
    }

    #[Test]
    public function it_permission_all_relationship_methods_exist_after_reorganization()
    {
        /** Arrange */
        $permission = new Permission();

        /** Act & Assert */
        $this->assertTrue(method_exists($permission, 'roles'), 'roles() should exist on Permission');
    }

    #[Test]
    public function it_permission_roles_returns_belongs_to_many_relationship()
    {
        /** Arrange */
        $permission = Permission::factory()->create();

        /** Act */
        $relationship = $permission->roles();

        /** Assert */
        $this->assertInstanceOf(BelongsToMany::class, $relationship);
    }

    // endregion
}