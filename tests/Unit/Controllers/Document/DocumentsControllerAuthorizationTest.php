<?php

namespace Tests\Unit\Controllers\Document;

use App\Models\Client;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\Storage\GetStorageProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('document_authorization')]
class DocumentsControllerAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;

    private User $otherUser;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Create owner user
        $this->owner = factory(User::class)->create();

        // Create another user who should NOT have access
        $this->otherUser = factory(User::class)->create();

        // Create a client owned by the owner
        $this->client = factory(Client::class)->create(['user_id' => $this->owner->id]);

        $this->bindFakeStorageProvider();
    }

    private function bindFakeStorageProvider(): void
    {
        $this->app->instance(GetStorageProvider::class, new class
        {
            public function getStorage(...$args)
            {
                return new class
                {
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
                        $document = $args[0] ?? null;

                        return response('', 200, [
                            'Content-Type' => $document?->mime ?? 'application/octet-stream',
                            'filename' => $document?->original_filename ?? '',
                        ]);
                    }

                    public function download(...$args)
                    {
                        $document = $args[0] ?? null;

                        return response('', 200, [
                            'Content-Type' => $document?->mime ?? 'application/octet-stream',
                            'filename' => $document?->original_filename ?? '',
                        ]);
                    }
                };
            }
        });
    }

    #[Test]
    public function user_can_view_document_attached_to_their_task_as_creator()
    {
        // Create a task created by owner
        $task = factory(Task::class)->create([
            'user_created_id' => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id, // Assigned to other user
            'client_id' => $this->client->id,
        ]);

        // Create document attached to task
        $document = factory(Document::class)->create([
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        // Verify document exists in database
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        // Owner should be able to view (they created the task)
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', $document->mime);
        $response->assertHeader('filename', $document->original_filename);
    }

    #[Test]
    public function user_can_view_document_attached_to_their_task_as_assignee()
    {
        // Create a task assigned to owner
        $task = factory(Task::class)->create([
            'user_created_id' => $this->otherUser->id, // Created by other user
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        // Create document attached to task
        $document = factory(Document::class)->create([
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
        $task = factory(Task::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id, // Owner's client
        ]);

        // Create document attached to task
        $document = factory(Document::class)->create([
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
        $otherClient = factory(Client::class)->create(['user_id' => $this->otherUser->id]);

        // Create a task owned by other user
        $task = factory(Task::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        // Create document attached to task
        $document = factory(Document::class)->create([
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
        $project = factory(Project::class)->create([
            'user_created_id' => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id,
        ]);

        $document = factory(Document::class)->create([
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
        $project = factory(Project::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        $document = factory(Document::class)->create([
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
        $otherClient = factory(Client::class)->create(['user_id' => $this->otherUser->id]);

        $project = factory(Project::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        $document = factory(Document::class)->create([
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
        $lead = factory(Lead::class)->create([
            'user_created_id' => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id,
        ]);

        $document = factory(Document::class)->create([
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
        $lead = factory(Lead::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        $document = factory(Document::class)->create([
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
        $otherClient = factory(Client::class)->create(['user_id' => $this->otherUser->id]);

        $lead = factory(Lead::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        $document = factory(Document::class)->create([
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
        $document = factory(Document::class)->create([
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
        $otherClient = factory(Client::class)->create(['user_id' => $this->otherUser->id]);

        $document = factory(Document::class)->create([
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
        $task = factory(Task::class)->create([
            'user_created_id' => $this->owner->id,
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        $document = factory(Document::class)->create([
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
        $otherClient = factory(Client::class)->create(['user_id' => $this->otherUser->id]);

        $task = factory(Task::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        $document = factory(Document::class)->create([
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
