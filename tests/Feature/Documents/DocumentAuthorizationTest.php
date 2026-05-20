<?php

namespace Tests\Feature\Documents;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('authorization-fix')]
class DocumentAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Task $task;

    private Project $project;

    private User $userWithTaskUploadPermission;

    private User $userWithProjectUploadPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_user_with_task_upload_permission_can_upload_files_to_task()
    {
        /* Arrange */
        $this->actingAs($this->userWithTaskUploadPermission);
        $file = UploadedFile::fake()->create('document.pdf', 100);

        /* Act */
        $response = $this->json('POST', route('document.task.upload', $this->task->external_id), [
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
        $response = $this->json('POST', route('document.task.upload', $this->task->external_id), [
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
        $response = $this->json('POST', route('document.project.upload', $this->project->external_id), [
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
        $response = $this->json('POST', route('document.project.upload', $this->project->external_id), [
            'files' => [$file],
        ]);

        /* Assert */
        $response->assertStatus(302);
    }
}
