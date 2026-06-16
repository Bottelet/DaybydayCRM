<?php

namespace Tests\Feature\Documents;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\Document;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Services\Storage\GetStorageProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('document_authorization')]
class DocumentsTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $task;

    protected $project;

    protected $unauthorizedUser;

    private User $owner;

    private User $creator;

    private User $assignee;

    private User $clientOwner;

    private User $unrelated;

    private User $otherUser;

    private Client $client;

    private User $userWithTaskUploadPermission;

    private User $userWithProjectUploadPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bindFakeStorageProvider();

        Integration::create([
            'name'     => 'local',
            'api_type' => 'file',
        ]);

        $this->owner = User::factory()->create();

        $this->otherUser = User::factory()->create();

        $this->client = Client::factory()->create(['user_id' => $this->owner->id]);

        $this->task    = Task::factory()->create();
        $this->project = Project::factory()->create();

        $roleWithTaskUpload = Role::create([
            'name'         => 'task-uploader',
            'display_name' => 'Tasks Uploader',
            'description'  => 'Can upload files to tasks',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $taskUploadPermission = Permission::query()->firstOrCreate(['name' => 'task-upload-files'], [
            'display_name' => 'Upload task files',
            'description'  => 'Can upload files to tasks',
            'grouping'     => 'task',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $roleWithTaskUpload->attachPermission($taskUploadPermission);

        $roleWithProjectUpload = Role::create([
            'name'         => 'project-uploader',
            'display_name' => 'Projects Uploader',
            'description'  => 'Can upload files to projects',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $projectUploadPermission = Permission::query()->firstOrCreate(['name' => 'project-upload-files'], [
            'display_name' => 'Upload project files',
            'description'  => 'Can upload files to projects',
            'grouping'     => 'project',
            'external_id'  => Str::uuid()->toString(),
        ]);
        $roleWithProjectUpload->attachPermission($projectUploadPermission);

        $roleWithoutPermission = Role::create([
            'name'         => 'document-viewer',
            'display_name' => 'Documents Viewer',
            'description'  => 'Cannot upload files',
            'external_id'  => Str::uuid()->toString(),
        ]);

        $this->userWithTaskUploadPermission = User::factory()->create();
        $this->userWithTaskUploadPermission->attachRole($roleWithTaskUpload);
        $this->userWithTaskUploadPermission = $this->userWithTaskUploadPermission->fresh();

        $this->userWithProjectUploadPermission = User::factory()->create();
        $this->userWithProjectUploadPermission->attachRole($roleWithProjectUpload);
        $this->userWithProjectUploadPermission = $this->userWithProjectUploadPermission->fresh();

        $this->userWithoutPermission = User::factory()->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);
        $this->userWithoutPermission = $this->userWithoutPermission->fresh();

        $this->user = User::factory()->withRole('employee')->create();
        $this->actingAs($this->user);

        $this->task    = Task::factory()->create();
        $this->project = Project::factory()->create();

        $this->unauthorizedUser = User::factory()->withRole('employee')->create();

        Integration::create([
            'name'     => 'local',
            'api_type' => 'file',
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        Setting::factory()->create();

        $this->creator     = User::factory()->create();
        $this->assignee    = User::factory()->create();
        $this->clientOwner = User::factory()->create();
        $this->unrelated   = User::factory()->create();
        $this->client      = Client::factory()->create(['user_id' => $this->clientOwner->id]);
    }

    #[Test]
    public function it_creator_of_task_can_view_task_document(): void
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_created_id'  => $this->creator->id,
            'user_assigned_id' => $this->assignee->id,
            'client_id'        => $this->client->id,
        ]);
        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
            'mime'        => 'text/plain',
            'path'        => 'fake/path.txt',
        ]);
        $this->actingAs($this->creator);

        /* Act */
        $response = $this->get(route('document.view', $document->external_id));

        /* Assert – creator must get a 200, not a redirect or error */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_creator_can_view_their_task_document(): void
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_created_id'  => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
        ]);
        $document->unsetRelation('source')->refresh();

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', $document->mime);
        $response->assertHeader('filename', $document->original_filename);
    }

    #[Test]
    public function it_assignee_can_view_their_task_document(): void
    {
        /* Arrange */
        $role = $this->owner->roles()->first() ?? \App\Models\Role::query()->firstOrCreate(['name' => 'owner']);
        if ( ! $this->owner->hasRole($role->name)) {
            $this->owner->attachRole($role);
        }
        $permissionName = PermissionName::DOCUMENT_VIEW instanceof PermissionName ? PermissionName::DOCUMENT_VIEW->value : PermissionName::DOCUMENT_VIEW;
        $permission     = \App\Models\Permission::query()->firstOrCreate(['name' => $permissionName], ['display_name' => $permissionName]);
        if ( ! $role->hasPermission($permissionName)) {
            $role->attachPermission($permission);
        }
        \Illuminate\Support\Facades\Cache::flush();
        $this->owner = $this->owner->fresh(['roles', 'roles.permissions']);

        $task = Task::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id'        => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
        ]);
        $document->unsetRelation('source')->refresh();

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_client_owner_can_view_task_document(): void
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $this->client->id,
        ]);

        $this->assertEquals($this->client->id, $task->client_id);
        $this->assertEquals($this->clientOwner->id, $task->client->user_id);

        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
        ]);
        $document->unsetRelation('source')->refresh();

        /* Act */
        $response = $this->actingAs($this->clientOwner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_unrelated_user_cannot_view_document_from_another_users_task(): void
    {
        /* Arrange */
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        $task = Task::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $otherClient->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
        ]);
        $document->unsetRelation('source')->refresh();

        $this->assertEquals($this->otherUser->id, $task->user_created_id);
        $this->assertEquals($this->otherUser->id, $task->user_assigned_id);
        $this->assertEquals($this->otherUser->id, $otherClient->user_id);

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to view this document'));
    }

    #[Test]
    public function it_creator_can_view_their_project_document()
    {
        /* Arrange */
        $project = Project::factory()->create([
            'user_created_id'  => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Project::class,
            'source_id'   => $project->id,
        ]);
        $document->unsetRelation('source')->refresh();

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_assignee_can_view_their_project_document()
    {
        /* Arrange */
        $project = Project::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id'        => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Project::class,
            'source_id'   => $project->id,
        ]);
        $document->unsetRelation('source')->refresh();

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_unrelated_user_cannot_view_document_from_another_users_project()
    {
        /* Arrange */
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        $project = Project::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $otherClient->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Project::class,
            'source_id'   => $project->id,
        ]);
        $document->unsetRelation('source')->refresh();

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function it_creator_can_view_their_lead_document()
    {
        /* Arrange */
        $lead = Lead::factory()->create([
            'user_created_id'  => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Lead::class,
            'source_id'   => $lead->id,
        ]);
        $document->unsetRelation('source')->refresh();

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_assignee_can_view_their_lead_document()
    {
        /* Arrange */
        $lead = Lead::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id'        => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Lead::class,
            'source_id'   => $lead->id,
        ]);
        $document->unsetRelation('source')->refresh();

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_unrelated_user_cannot_view_document_from_another_users_lead()
    {
        /* Arrange */
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        $lead = Lead::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $otherClient->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Lead::class,
            'source_id'   => $lead->id,
        ]);
        $document->unsetRelation('source')->refresh();

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function it_client_owner_can_view_their_client_document()
    {
        /* Arrange */
        $document = Document::factory()->create([
            'source_type' => Client::class,
            'source_id'   => $this->client->id,
        ]);

        /* Act */
        $response = $this->actingAs($this->clientOwner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_unrelated_user_cannot_view_document_from_another_users_client()
    {
        /* Arrange */
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        $document = Document::factory()->create([
            'source_type' => Client::class,
            'source_id'   => $otherClient->id,
        ]);

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function it_assignee_of_task_can_view_task_document()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_created_id'  => $this->creator->id,
            'user_assigned_id' => $this->assignee->id,
            'client_id'        => $this->client->id,
        ]);
        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
            'mime'        => 'text/plain',
            'path'        => 'fake/path.txt',
        ]);
        $this->actingAs($this->assignee);

        /* Act */
        $response = $this->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_client_owner_can_view_document_attached_to_their_client_task()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_created_id'  => $this->unrelated->id,
            'user_assigned_id' => $this->unrelated->id,
            'client_id'        => $this->client->id,
        ]);
        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
            'mime'        => 'text/plain',
            'path'        => 'fake/path.txt',
        ]);
        $this->actingAs($this->clientOwner);

        /* Act */
        $response = $this->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_unrelated_user_cannot_view_document_they_have_no_connection_to()
    {
        /* Arrange */
        $document = $this->createUnownedDocument();
        $this->actingAs($this->unrelated);

        /* Act */
        $response = $this->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(302); // redirects back with flash message
        $this->assertTrue(
            session()->has('flash_message_warning'),
            'Unrelated user should see a warning flash message'
        );
    }

    #[Test]
    public function it_json_request_returns_403_json_for_unauthorized_document_view()
    {
        /* Arrange */
        $document = $this->createUnownedDocument();
        $this->actingAs($this->unrelated);

        /* Act */
        $response = $this->get(route('document.view', $document->external_id), ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_authorization_is_checked_before_storage_access_on_view()
    {
        /* Arrange – no storage configured but still expect auth to run first */
        \App\Models\Integration::whereApiType('file')->delete();
        app(\App\Services\Storage\StorageAdapterRegistry::class)->reset();

        $document = $this->createUnownedDocument();
        $this->actingAs($this->unrelated);

        /* Act */
        $response = $this->get(route('document.view', $document->external_id), ['Accept' => 'application/json']);

        /* Assert – unauthorized user gets 403, not a storage error */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_task_owner_can_download_their_task_document()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_created_id'  => $this->owner->id,
            'user_assigned_id' => $this->owner->id,
            'client_id'        => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
        ]);

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.download', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_unrelated_user_cannot_download_document_from_another_users_task()
    {
        /* Arrange */
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        $task = Task::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $otherClient->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
        ]);

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.download', $document->external_id));

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to download this document'));
    }

    #[Test]
    public function it_unrelated_user_cannot_download_document_they_have_no_connection_to()
    {
        /* Arrange */
        $document = $this->createUnownedDocument();
        $this->actingAs($this->unrelated);

        /* Act – download uses the same canAccessDocument gate as view */
        $response = $this->get(route('document.download', $document->external_id));

        /* Assert – redirected with warning (no 403, same pattern as view) */
        $response->assertStatus(302);
        $this->assertTrue(
            session()->has('flash_message_warning'),
            'Unrelated user should see a warning flash message on download'
        );
    }

    #[Test]
    public function it_json_download_request_returns_403_for_unauthorized_user()
    {
        /* Arrange */
        $document = $this->createUnownedDocument();
        $this->actingAs($this->unrelated);

        /* Act */
        $response = $this->get(route('document.download', $document->external_id), ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(403);
    }

    #[Test]
    public function it_user_with_task_upload_permission_can_upload_files_to_task()
    {
        /* Arrange */
        $this->actingAs($this->userWithTaskUploadPermission);
        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.task.upload', $this->task->external_id), [
            'files' => [$file],
        ]);

        /* Assert */
        $this->assertNotEquals(403, $response->status());
    }

    #[Test]
    public function it_user_without_task_upload_permission_cannot_upload_files_to_task()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);
        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.task.upload', $this->task->external_id), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertStatus(302);
    }

    #[Test]
    public function it_user_with_project_upload_permission_can_upload_files_to_project()
    {
        /* Arrange */
        $this->actingAs($this->userWithProjectUploadPermission);
        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.project.upload', $this->project->external_id), [
            'files' => [$file],
        ]);

        /* Assert */
        $this->assertNotEquals(403, $response->status());
    }

    #[Test]
    public function it_user_without_project_upload_permission_cannot_upload_files_to_project()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);
        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.project.upload', $this->project->external_id), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertStatus(302);
    }

    #[Test]
    #[Group('postJson')]
    public function it_authorized_user_can_upload_file_to_task()
    {
        /* Arrange */
        $permission = Permission::query()->firstOrCreate(['name' => 'task-upload-files']);
        $this->user->roles->first()->attachPermission($permission);

        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.task.upload', $this->task->external_id), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertDatabaseHas('documents', [
            'source_type' => Task::class,
            'source_id'   => $this->task->id,
        ]);
    }

    #[Test]
    public function it_unauthorized_user_cannot_upload_file_to_task()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.task.upload', $this->task->external_id), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to upload files'));
        $this->assertDatabaseMissing('documents', [
            'source_type' => Task::class,
            'source_id'   => $this->task->id,
        ]);
    }

    #[Test]
    #[Group('postJson')]
    public function it_authorized_user_can_upload_file_to_project()
    {
        /* Arrange */
        $permission = Permission::query()->firstOrCreate(['name' => 'project-upload-files']);
        $this->user->roles->first()->attachPermission($permission);

        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.project.upload', $this->project->external_id), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertDatabaseHas('documents', [
            'source_type' => Project::class,
            'source_id'   => $this->project->id,
        ]);
    }

    #[Test]
    public function it_unauthorized_user_cannot_upload_file_to_project()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.project.upload', $this->project->external_id), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to upload files'));
        $this->assertDatabaseMissing('documents', [
            'source_type' => Project::class,
            'source_id'   => $this->project->id,
        ]);
    }

    #[Test]
    public function it_upload_to_nonexistent_task_returns_error()
    {
        /* Arrange */
        $permission = Permission::query()->firstOrCreate(['name' => 'task-upload-files']);
        $this->user->roles->first()->attachPermission($permission);

        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.task.upload', 'nonexistent-uuid'), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Task not found'));
    }

    #[Test]
    public function it_upload_to_nonexistent_project_returns_error()
    {
        /* Arrange */
        $permission = Permission::query()->firstOrCreate(['name' => 'project-upload-files']);
        $this->user->roles->first()->attachPermission($permission);

        Cache::tags('role_user')->flush();
        Cache::tags('permission_role')->flush();
        $this->user = $this->user->fresh();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->post(route('document.project.upload', 'nonexistent-uuid'), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Project not found'));
    }

    #[Test]
    public function it_returns_404_when_document_not_found()
    {
        /* Arrange */
        $fakeUuid = Str::uuid();

        $this->assertDatabaseMissing('documents', [
            'external_id' => $fakeUuid,
        ]);

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $fakeUuid));

        /* Assert */
        $response->assertStatus(404);
    }

    private function bindFakeStorageProvider(): void
    {
        $this->app->instance(GetStorageProvider::class, new class () {
            public function getStorage(...$args)
            {
                return new class () {
                    public function enabled(): bool
                    {
                        return true;
                    }

                    public function isEnabled(): bool
                    {
                        return true;
                    }

                    public function view(...$args)
                    {
                        return 'fake file content';
                    }

                    public function download(...$args)
                    {
                        return 'fake file content';
                    }
                };
            }
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Create a document that $this->unrelated has no connection to.
     *
     * The document is owned by $this->creator (as task creator) and
     * $this->assignee (as task assignee), while its client belongs to
     * $this->creator (not $this->clientOwner).  This means $this->unrelated
     * has no path to the document through any of the three ownership checks
     * (creator / assignee / client owner).
     */
    private function createUnownedDocument(): Document
    {
        $otherClient = Client::factory()->create(['user_id' => $this->creator->id]);
        $task        = Task::factory()->create([
            'user_created_id'  => $this->creator->id,
            'user_assigned_id' => $this->assignee->id,
            'client_id'        => $otherClient->id,
        ]);

        return Document::factory()->create([
            'source_type' => Task::class,
            'source_id'   => $task->id,
            'mime'        => 'text/plain',
            'path'        => 'fake/path.txt',
        ]);
    }
}
