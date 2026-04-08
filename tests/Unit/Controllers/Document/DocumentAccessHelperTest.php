<?php

namespace Tests\Unit\Controllers\Document;

use App\Models\Client;
use App\Models\Document;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('document_authorization')]
class DocumentAccessHelperTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;
    private User $otherUser;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = factory(User::class)->create();
        $this->otherUser = factory(User::class)->create();
        $this->client = factory(Client::class)->create(['user_id' => $this->owner->id]);
    }

    #[Test]
    public function helper_method_correctly_identifies_ownership_via_creator()
    {
        $task = factory(Task::class)->create([
            'user_created_id' => $this->owner->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id,
        ]);

        $document = factory(Document::class)->create([
            'source_type' => Task::class,
            'source_id' => $task->id,
        ]);

        // Test via reflection to access private method
        $controller = new \App\Http\Controllers\DocumentsController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        $this->actingAs($this->owner);
        $result = $method->invokeArgs($controller, [$task, $this->owner]);
        
        $this->assertTrue($result, 'Owner should have access via user_created_id');
    }

    #[Test]
    public function helper_method_correctly_identifies_ownership_via_assignee()
    {
        $task = factory(Task::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id' => $this->client->id,
        ]);

        $controller = new \App\Http\Controllers\DocumentsController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        $this->actingAs($this->owner);
        $result = $method->invokeArgs($controller, [$task, $this->owner]);
        
        $this->assertTrue($result, 'Owner should have access via user_assigned_id');
    }

    #[Test]
    public function helper_method_correctly_identifies_ownership_via_client()
    {
        $task = factory(Task::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $this->client->id,
        ]);

        $controller = new \App\Http\Controllers\DocumentsController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        $this->actingAs($this->owner);
        // Need to load the client relationship
        $task->load('client');
        $result = $method->invokeArgs($controller, [$task, $this->owner]);
        
        $this->assertTrue($result, 'Owner should have access via client ownership');
    }

    #[Test]
    public function helper_method_correctly_denies_access_to_non_owner()
    {
        $otherClient = factory(Client::class)->create(['user_id' => $this->otherUser->id]);
        $task = factory(Task::class)->create([
            'user_created_id' => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        $controller = new \App\Http\Controllers\DocumentsController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        $this->actingAs($this->owner);
        $task->load('client');
        $result = $method->invokeArgs($controller, [$task, $this->owner]);
        
        $this->assertFalse($result, 'Owner should NOT have access to other user\'s task');
    }
}
