<?php

namespace Tests\Feature\Documents;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Integration;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('document-controller')]
class DocumentSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $task;

    protected $project;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    #[Test]
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
        $response = $this->json('POST', route('document.task.upload', $this->task->external_id), [
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
        $response = $this->json('POST', route('document.task.upload', $this->task->external_id), [
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
        $response = $this->json('POST', route('document.project.upload', $this->project->external_id), [
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
        $response = $this->json('POST', route('document.project.upload', $this->project->external_id), [
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
        $response = $this->json('POST', route('document.task.upload', 'nonexistent-uuid'), [
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
        $response = $this->json('POST', route('document.project.upload', 'nonexistent-uuid'), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Project not found'));
    }
}
