<?php

namespace Tests\Feature\Documents;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\Document;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Tests for document access control.
 *
 * These tests verify the *observable behavior* of document access rules
 * (ownership through creator / assignee / client) via HTTP requests instead
 * of testing private controller methods through reflection.
 *
 * Testing private methods via reflection couples tests to implementation
 * details and breaks as soon as the code is refactored.  Testing the HTTP
 * response is the correct level of abstraction.
 */
#[Group('security')]
#[Group('document_authorization')]
class DocumentAccessHelperTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $creator;

    private User $assignee;

    private User $clientOwner;

    private User $unrelated;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        \App\Models\Setting::factory()->create();

        $this->creator     = User::factory()->create();
        $this->assignee    = User::factory()->create();
        $this->clientOwner = User::factory()->create();
        $this->unrelated   = User::factory()->create();
        $this->client      = Client::factory()->create(['user_id' => $this->clientOwner->id]);
    }

    // ─── Positive-path access ─────────────────────────────────────────────────

    #[Test]
    public function it_creator_of_task_can_view_task_document()
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

    // ─── Negative-path access ─────────────────────────────────────────────────

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
        $response = $this->getJson(route('document.view', $document->external_id));

        /* Assert */
        $response->assertStatus(403);
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
        $response = $this->getJson(route('document.download', $document->external_id));

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
        $response = $this->getJson(route('document.view', $document->external_id));

        /* Assert – unauthorized user gets 403, not a storage error */
        $response->assertStatus(403);
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
