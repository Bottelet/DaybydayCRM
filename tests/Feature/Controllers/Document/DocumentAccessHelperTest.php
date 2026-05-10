<?php

namespace Tests\Feature\Controllers\Document;

use App\Http\Controllers\DocumentsController;
use App\Models\Client;
use App\Models\Document;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('document_authorization')]
class DocumentAccessHelperTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $otherUser;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner     = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->client    = Client::factory()->create(['user_id' => $this->owner->id]);
    }

    #[Test]
    public function it_helper_method_correctly_identifies_ownership_via_creator()
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

        $controller = new DocumentsController();
        $reflection = new ReflectionClass($controller);
        $method     = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        /* Act */
        $this->actingAs($this->owner);
        $result = $method->invokeArgs($controller, [$task, $this->owner]);

        /* Assert */
        $this->assertTrue($result, 'Owner should have access via user_created_id');
    }

    #[Test]
    public function it_helper_method_correctly_identifies_ownership_via_assignee()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->owner->id,
            'client_id'        => $this->client->id,
        ]);

        $controller = new DocumentsController();
        $reflection = new ReflectionClass($controller);
        $method     = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        /* Act */
        $this->actingAs($this->owner);
        $result = $method->invokeArgs($controller, [$task, $this->owner]);

        /* Assert */
        $this->assertTrue($result, 'Owner should have access via user_assigned_id');
    }

    #[Test]
    public function it_helper_method_correctly_identifies_ownership_via_client()
    {
        /* Arrange */
        $task = Task::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $this->client->id,
        ]);

        $controller = new DocumentsController();
        $reflection = new ReflectionClass($controller);
        $method     = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        /* Act */
        $this->actingAs($this->owner);
        $task->load('client');
        $result = $method->invokeArgs($controller, [$task, $this->owner]);

        /* Assert */
        $this->assertTrue($result, 'Owner should have access via client ownership');
    }

    #[Test]
    public function it_helper_method_correctly_denies_access_to_non_owner()
    {
        /* Arrange */
        $otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);
        $task        = Task::factory()->create([
            'user_created_id'  => $this->otherUser->id,
            'user_assigned_id' => $this->otherUser->id,
            'client_id'        => $otherClient->id,
        ]);

        $controller = new DocumentsController();
        $reflection = new ReflectionClass($controller);
        $method     = $reflection->getMethod('userOwnsAssignableSource');
        $method->setAccessible(true);

        /* Act */
        $this->actingAs($this->owner);
        $task->load('client');
        $result = $method->invokeArgs($controller, [$task, $this->owner]);

        /* Assert */
        $this->assertFalse($result, 'Owner should NOT have access to other user\'s task');
    }
}
