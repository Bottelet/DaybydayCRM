<?php

namespace Tests\Unit\Controllers\Document;

use App\Http\Controllers\DocumentsController;
use App\Models\Client;
use App\Models\Document;
use App\Models\Task;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;

#[Group('security')]
#[Group('document_authorization')]
class DocumentAccessHelperAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherUser;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->owner->id]);
    }

    #[Test]
    public function helper_method_correctly_identifies_ownership_via_creator()
    {
        $task = Task::factory()->create([
            'user_created_id' => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id,
        ]);

        $document = Document::factory()->create([
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        // Use reflection to test private helper method
        // Testing private methods via reflection allows us to verify the helper's logic in isolation,
        // providing granular test coverage beyond what's possible through the public API alone.
        // The helper method is intentionally private as it's an internal implementation detail.
        $controller = new DocumentsController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        $this->actingAs($this->owner);
        $result = $method->invokeArgs($controller, [$task, $this->owner]);

        $this->assertTrue($result, 'Owner should have access via user_created_id');
    }

    #[Test]
    public function helper_method_correctly_identifies_ownership_via_assignee()
    {
        $task = Task::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        $controller = new DocumentsController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        $this->actingAs($this->owner);
        $result = $method->invokeArgs($controller, [$task, $this->owner]);

        $this->assertTrue($result, 'Owner should have access via user_assigned_id');
    }

    #[Test]
    public function helper_method_correctly_identifies_ownership_via_client()
    {
        $task = Task::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id,
        ]);

        $controller = new DocumentsController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        $this->actingAs($this->owner);
        // Eager load the client relationship since the helper method checks $source->client->user_id
        // Without loading, accessing the relationship could cause a query or null reference
        $task->load('client');
        $result = $method->invokeArgs($controller, [$task, $this->owner]);

        $this->assertTrue($result, 'Owner should have access via client ownership');
    }

    #[Test]
    public function helper_method_correctly_denies_access_to_non_owner()
    {
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);
        $task = Task::factory()->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        $controller = new DocumentsController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        $this->actingAs($this->owner);
        $task->load('client');
        $result = $method->invokeArgs($controller, [$task, $this->owner]);

        $this->assertFalse($result, 'Owner should NOT have access to other user\'s task');
    }
}
