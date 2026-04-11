<?php

namespace Tests\Unit\Controllers\Document;

use App\Enums\PermissionName;
use App\Models\Client;
use App\Models\Document;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\Storage\GetStorageProvider;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('security')]
#[Group('document_authorization')]
class DocumentsControllerAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherUser;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        // CRITICAL: Bind fake storage provider BEFORE creating users/entities
        // The filesystem middleware checks this during requests
        $this->bindFakeStorageProvider();

        // Create file storage integration so the filesystem middleware passes
        Integration::create([
            'name' => 'local',
            'api_type' => 'file',
        ]);

        // Create owner user
        $this->owner = User::factory()->create();

        // Create another user who should NOT have access
        $this->otherUser = User::factory()->create();

        // Create a client owned by the owner
        $this->client = Client::factory()->create(['user_id' => $this->owner->id]);
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
                        // Return file content (string), not a response object
                        return 'fake file content';
                    }

                    public function download(...$args)
                    {
                        // Return file content (string), not a response object
                        return 'fake file content';
                    }
                };
            }
        });
    }

    #[Test]
    public function user_can_view_document_attached_to_their_task_as_creator()
    {
        $this->markTestIncomplete('File is not present so document view breaks');
        // 1. Grant
        $this->withPermissions(PermissionName::DOCUMENT_VIEW);

        // 2. Build
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $task = Task::factory()->create([
            'user_created_id' => $owner->id,
            'user_assigned_id' => $this->otherUser->id, // Assigned to other user
            'client_id' => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Task::class, // Use the class string, not new Task()->getMorphClass()
            'source_id' => $task->id,
        ]);

        // 3. Fresh State
        $this->actingAs($owner->fresh());

        // 4. Request
        $response = $this->get(route('document.view', $document->external_id));

        dd($response->status());

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', $document->mime);
        $response->assertHeader('filename', $document->original_filename);
    }

    #[Test]
    public function user_can_view_document_attached_to_their_task_as_assignee()
    {
        // Create a task assigned to owner
        $task = Task::factory()->create([
            'user_created_id' => $this->otherUser->id, // Created by other user
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        // Create document attached to task
        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        // Owner should be able to view (they are assigned to the task)
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_view_document_attached_to_task_via_client_ownership()
    {
        // Create a task on owner's client but created/assigned to others
        $task = Task::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id, // Owner's client
        ]);

        // Verify task has the correct client_id
        $this->assertEquals($this->client->id, $task->client_id);
        $this->assertEquals($this->owner->id, $task->client->user_id);

        // Create document attached to task
        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        // Owner should be able to view (they own the client)
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_cannot_view_document_attached_to_another_users_task()
    {
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        // Create a task owned by other user
        $task = Task::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        // Create document attached to task
        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        // Verify task and document are owned by other user
        $this->assertEquals($this->otherUser->id, $task->user_created_id);
        $this->assertEquals($this->otherUser->id, $task->user_assigned_id);
        $this->assertEquals($this->otherUser->id, $otherClient->user_id);

        // Owner should NOT be able to view
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to view this document'));
    }

    #[Test]
    public function user_can_view_document_attached_to_their_project_as_creator()
    {
        $project = Project::factory()->create([
            'user_created_id' => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Project::class,
            'source_id' => $project->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_view_document_attached_to_their_project_as_assignee()
    {
        $project = Project::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Project::class,
            'source_id' => $project->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_cannot_view_document_attached_to_another_users_project()
    {
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        $project = Project::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Project::class,
            'source_id' => $project->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function user_can_view_document_attached_to_their_lead_as_creator()
    {
        $lead = Lead::factory()->create([
            'user_created_id' => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Lead::class,
            'source_id' => $lead->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_view_document_attached_to_their_lead_as_assignee()
    {
        $lead = Lead::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Lead::class,
            'source_id' => $lead->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_cannot_view_document_attached_to_another_users_lead()
    {
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        $lead = Lead::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Lead::class,
            'source_id' => $lead->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function user_can_view_document_attached_to_their_client()
    {
        $document = Document::factory()->create([
            'source_type' => Client::class,
            'source_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_cannot_view_document_attached_to_another_users_client()
    {
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        $document = Document::factory()->create([
            'source_type' => Client::class,
            'source_id' => $otherClient->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function user_can_download_document_attached_to_their_task()
    {
        $task = Task::factory()->create([
            'user_created_id' => $this->owner->id,
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.download', $document->external_id));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_cannot_download_document_attached_to_another_users_task()
    {
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);

        $task = Task::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.download', $document->external_id));

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to download this document'));
    }

    #[Test]
    public function returns_404_when_document_not_found()
    {
        $fakeUuid = Str::uuid();

        // Verify document doesn't exist in database
        $this->assertDatabaseMissing('documents', [
            'external_id' => $fakeUuid,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $fakeUuid));

        $response->assertStatus(404);
    }
}
