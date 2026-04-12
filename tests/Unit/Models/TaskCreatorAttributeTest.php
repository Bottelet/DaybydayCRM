<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Regression tests for Task::getCreatorUserAttribute().
 *
 * Before the fix, getCreatorUserAttribute() used $this->user_assigned_id (the assignee)
 * instead of $this->user_created_id (the creator). This caused the creator_user accessor
 * to return the wrong user when creator and assignee were different people.
 */
#[Group('regression')]
class TaskCreatorAttributeTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $creator;

    private User $assignee;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->client = Client::factory()->create();
        $this->creator = User::factory()->create();
        $this->assignee = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region happy_path

    #[Test]
    public function it_creator_user_attribute_returns_the_creator_not_the_assignee()
    {
        /** Arrange */
        // Create a task where creator and assignee are different users
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'user_created_id' => $this->creator->id,
            'user_assigned_id' => $this->assignee->id,
        ]);

        /** Act */
        $creatorUser = $task->creator_user;

        /** Assert */
        $this->assertNotNull($creatorUser);
        $this->assertEquals(
            $this->creator->id,
            $creatorUser->id,
            'creator_user should return the user who created the task (user_created_id), not the assignee'
        );
        $this->assertNotEquals(
            $this->assignee->id,
            $creatorUser->id,
            'creator_user must NOT return the assignee (user_assigned_id)'
        );
    }

    #[Test]
    public function it_creator_user_attribute_differs_from_assigned_user_attribute_when_different()
    {
        /** Arrange */
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'user_created_id' => $this->creator->id,
            'user_assigned_id' => $this->assignee->id,
        ]);

        /** Act */
        $assignedUser = $task->assigned_user;
        $creatorUser = $task->creator_user;

        /** Assert */
        $this->assertNotEquals(
            $creatorUser->id,
            $assignedUser->id,
            'creator_user and assigned_user should be different when created and assigned by different users'
        );
        $this->assertEquals($this->creator->id, $creatorUser->id);
        $this->assertEquals($this->assignee->id, $assignedUser->id);
    }

    #[Test]
    public function it_creator_user_attribute_returns_same_user_when_creator_and_assignee_are_same()
    {
        /** Arrange */
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'user_created_id' => $this->creator->id,
            'user_assigned_id' => $this->creator->id,
        ]);

        /** Act */
        $creatorUser = $task->creator_user;
        $assignedUser = $task->assigned_user;

        /** Assert */
        $this->assertEquals($this->creator->id, $creatorUser->id);
        $this->assertEquals($this->creator->id, $assignedUser->id);
        $this->assertEquals($creatorUser->id, $assignedUser->id);
    }

    // endregion

    // region regression

    #[Test]
    #[Group('regression')]
    public function it_creator_user_attribute_uses_user_created_id_not_user_assigned_id()
    {
        /** Arrange */
        // This test explicitly guards against the regression where user_assigned_id was used
        // instead of user_created_id in getCreatorUserAttribute().
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'user_created_id' => $this->creator->id,
            'user_assigned_id' => $this->assignee->id,
        ]);

        /** Act */
        $creatorUser = $task->creator_user;

        /** Assert */
        // The creator_user MUST reflect user_created_id
        $this->assertEquals(
            $task->user_created_id,
            $creatorUser->id,
            'getCreatorUserAttribute() must use user_created_id, not user_assigned_id'
        );

        // Guard: ensure it is NOT returning the assignee's ID (which was the bug)
        $this->assertNotEquals(
            $task->user_assigned_id,
            $creatorUser->id,
            'Bug regression: getCreatorUserAttribute() was incorrectly using user_assigned_id'
        );
    }

    #[Test]
    #[Group('regression')]
    public function it_creator_relationship_and_creator_user_attribute_resolve_to_same_user()
    {
        /** Arrange */
        $task = Task::factory()->create([
            'client_id' => $this->client->id,
            'user_created_id' => $this->creator->id,
            'user_assigned_id' => $this->assignee->id,
        ]);

        /** Act */
        $creatorFromRelationship = $task->creator;   // BelongsTo relationship
        $creatorFromAttribute = $task->creator_user; // Magic accessor

        /** Assert */
        $this->assertNotNull($creatorFromRelationship);
        $this->assertNotNull($creatorFromAttribute);
        $this->assertEquals(
            $creatorFromRelationship->id,
            $creatorFromAttribute->id,
            'creator() relationship and creator_user accessor must return the same user'
        );
    }

    // endregion
}