<?php

namespace Tests\Feature\Documents;

use App\Enums\PermissionName;
use App\Models\Client;
use App\Models\Document;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\Storage\GetStorageProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

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

        $this->bindFakeStorageProvider();

        Integration::create([
            'name'     => 'local',
            'api_type' => 'file',
        ]);

        $this->owner = User::factory()->create();

        $this->otherUser = User::factory()->create();

        $this->client = Client::factory()->create(['user_id' => $this->owner->id]);
    }

    #[Test]
    public function it_user_can_view_document_attached_to_their_task_as_creator()
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
    public function it_user_can_view_document_attached_to_their_task_as_assignee()
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
    public function it_user_can_view_document_attached_to_task_via_client_ownership()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $this->client->id,
        ]);

        $this->assertEquals($this->client->id, $task->client_id);
        $this->assertEquals($this->owner->id, $task->client->user_id);

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
    public function it_user_cannot_view_document_attached_to_another_users_task()
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
    public function it_user_can_view_document_attached_to_their_project_as_creator()
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
    public function it_user_can_view_document_attached_to_their_project_as_assignee()
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
    public function it_user_cannot_view_document_attached_to_another_users_project()
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
    public function it_user_can_view_document_attached_to_their_lead_as_creator()
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
    public function it_user_can_view_document_attached_to_their_lead_as_assignee()
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
    public function it_user_cannot_view_document_attached_to_another_users_lead()
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
    public function it_user_can_view_document_attached_to_their_client()
    {
        /* Arrange */
        $document = Document::factory()->create([
            'source_type' => Client::class,
            'source_id'   => $this->client->id,
        ]);

        /* Act */
        $response = $this->actingAs($this->owner)
            ->get(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_user_cannot_view_document_attached_to_another_users_client()
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
    public function it_user_can_download_document_attached_to_their_task()
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
    public function it_user_cannot_download_document_attached_to_another_users_task()
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
}
