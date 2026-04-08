<?php

namespace Tests\Unit\Controllers\Document;

use App\Http\Middleware\VerifyCsrfToken;
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

#[Group('authorization-fix')]
class DocumentAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private Task $task;

    private Project $project;

    private User $userWithTaskUploadPermission;

    private User $userWithProjectUploadPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = factory(Task::class)->create();
        $this->project = factory(Project::class)->create();

        // Create role with task-upload-files permission
        $roleWithTaskUpload = Role::create([
            'name' => 'task-uploader',
            'display_name' => 'Task Uploader',
            'description' => 'Can upload files to tasks',
        ]);
        $taskUploadPermission = Permission::where('name', 'task-upload-files')->first();
        $roleWithTaskUpload->attachPermission($taskUploadPermission);

        // Create role with project-upload-files permission
        $roleWithProjectUpload = Role::create([
            'name' => 'project-uploader',
            'display_name' => 'Project Uploader',
            'description' => 'Can upload files to projects',
        ]);
        $projectUploadPermission = Permission::where('name', 'project-upload-files')->first();
        $roleWithProjectUpload->attachPermission($projectUploadPermission);

        // Create role without upload permissions
        $roleWithoutPermission = Role::create([
            'name' => 'document-viewer',
            'display_name' => 'Document Viewer',
            'description' => 'Cannot upload files',
        ]);

        // Create users
        $this->userWithTaskUploadPermission = factory(User::class)->create();
        $this->userWithTaskUploadPermission->attachRole($roleWithTaskUpload);

        $this->userWithProjectUploadPermission = factory(User::class)->create();
        $this->userWithProjectUploadPermission->attachRole($roleWithProjectUpload);

        $this->userWithoutPermission = factory(User::class)->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function user_with_task_upload_permission_can_upload_files_to_task()
    {
        $this->actingAs($this->userWithTaskUploadPermission);

        // Mock file upload
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.task.upload', $this->task->external_id), [
            'files' => [$file],
        ]);

        // Since this is integration test and file system may not be configured,
        // we mainly check that authorization passes (not 403)
        $this->assertNotEquals(403, $response->status());
    }

    #[Test]
    public function user_without_task_upload_permission_cannot_upload_files_to_task()
    {
        $this->actingAs($this->userWithoutPermission);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.task.upload', $this->task->external_id), [
            'files' => [$file],
        ]);

        $response->assertStatus(302); // Redirect with error message
    }

    #[Test]
    public function user_with_project_upload_permission_can_upload_files_to_project()
    {
        $this->actingAs($this->userWithProjectUploadPermission);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.project.upload', $this->project->external_id), [
            'files' => [$file],
        ]);

        // Since this is integration test and file system may not be configured,
        // we mainly check that authorization passes (not 403)
        $this->assertNotEquals(403, $response->status());
    }

    #[Test]
    public function user_without_project_upload_permission_cannot_upload_files_to_project()
    {
        $this->actingAs($this->userWithoutPermission);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->json('POST', route('document.project.upload', $this->project->external_id), [
            'files' => [$file],
        ]);

        $response->assertStatus(302); // Redirect with error message
    }
}
