<?php

namespace Tests\Unit\Controllers\Document;

use App\Models\Integration;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('document-controller')]
class DocumentSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected $task;

    protected $project;

    protected $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = factory(Task::class)->create();
        $this->project = factory(Project::class)->create();

        // Create a user without upload permissions
        $this->unauthorizedUser = factory(User::class)->create();
        $role = Role::where('name', 'employee')->first();
        $this->unauthorizedUser->attachRole($role);

        // Mock file storage integration
        Integration::create([
            'name' => 'local',
            'api_type' => 'file',
        ]);
    }

    #[Test]
    public function authorized_user_can_upload_file_to_task()
    {
        // Give user permission to upload files to tasks
        $permission = Permission::firstOrCreate(['name' => 'task-upload-files']);
        $this->user->roles->first()->attachPermission($permission);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.task.upload', $this->task->external_id), [
            'files' => [$file],
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthorized_user_cannot_upload_file_to_task()
    {
        $this->actingAs($this->unauthorizedUser);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.task.upload', $this->task->external_id), [
            'files' => [$file],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to upload files'));
    }

    #[Test]
    public function authorized_user_can_upload_file_to_project()
    {
        // Give user permission to upload files to projects
        $permission = Permission::firstOrCreate(['name' => 'project-upload-files']);
        $this->user->roles->first()->attachPermission($permission);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.project.upload', $this->project->external_id), [
            'files' => [$file],
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthorized_user_cannot_upload_file_to_project()
    {
        $this->actingAs($this->unauthorizedUser);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.project.upload', $this->project->external_id), [
            'files' => [$file],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to upload files'));
    }

    #[Test]
    public function upload_to_nonexistent_task_returns_error()
    {
        $permission = Permission::firstOrCreate(['name' => 'task-upload-files']);
        $this->user->roles->first()->attachPermission($permission);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.task.upload', 'nonexistent-uuid'), [
            'files' => [$file],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Task not found'));
    }

    #[Test]
    public function upload_to_nonexistent_project_returns_error()
    {
        $permission = Permission::firstOrCreate(['name' => 'project-upload-files']);
        $this->user->roles->first()->attachPermission($permission);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.project.upload', 'nonexistent-uuid'), [
            'files' => [$file],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('Project not found'));
    }
}
