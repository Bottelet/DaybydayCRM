<?php

namespace Tests\Unit\Models;

use App\Models\Activity;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Tests for Activity model relationships after alphabetical reorganization.
 * Verifies that the reordering of relationship methods (causer, source, task, user)
 * did not break any relationship functionality.
 *
 * Note: The activities table only has columns for causer_id/causer_type and
 * source_id/source_type. The task() and user() relationships reference columns
 * (task_id, user_id) not present in the activities table, so only their
 * relationship type (not resolution) is validated here.
 */
class ActivityModelRelationshipsTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $user;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->user = User::factory()->create();
        $this->task = Task::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    //region happy_path

    #[Test]
    public function activity_causer_relationship_returns_morph_to_instance()
    {
        /** Arrange */
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        /** Act */
        $relationship = $activity->causer();

        /** Assert */
        $this->assertInstanceOf(MorphTo::class, $relationship);
    }

    #[Test]
    public function activity_causer_resolves_to_user_model()
    {
        /** Arrange */
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        /** Act */
        $causer = $activity->causer;

        /** Assert */
        $this->assertNotNull($causer);
        $this->assertInstanceOf(User::class, $causer);
        $this->assertEquals($this->user->id, $causer->id);
    }

    #[Test]
    public function activity_source_relationship_returns_morph_to_instance()
    {
        /** Arrange */
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        /** Act */
        $relationship = $activity->source();

        /** Assert */
        $this->assertInstanceOf(MorphTo::class, $relationship);
    }

    #[Test]
    public function activity_source_resolves_to_task_model()
    {
        /** Arrange */
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        /** Act */
        $source = $activity->source;

        /** Assert */
        $this->assertNotNull($source);
        $this->assertInstanceOf(Task::class, $source);
        $this->assertEquals($this->task->id, $source->id);
    }

    #[Test]
    public function activity_task_relationship_method_returns_belongs_to_instance()
    {
        /** Arrange */
        // task() uses 'task_id' foreign key. The column doesn't exist in the DB table,
        // but the BelongsTo relationship object can still be instantiated.
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        /** Act */
        $relationship = $activity->task();

        /** Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
    }

    #[Test]
    public function activity_user_relationship_method_returns_belongs_to_instance()
    {
        /** Arrange */
        // user() uses 'user_id' foreign key. The column doesn't exist in the DB table,
        // but the BelongsTo relationship object can still be instantiated.
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        /** Act */
        $relationship = $activity->user();

        /** Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
    }

    //endregion

    //region edge_cases

    #[Test]
    public function activity_all_relationship_methods_exist()
    {
        /** Arrange */
        $activity = new Activity();

        /** Act & Assert */
        $this->assertTrue(method_exists($activity, 'causer'), 'causer() relationship method should exist');
        $this->assertTrue(method_exists($activity, 'source'), 'source() relationship method should exist');
        $this->assertTrue(method_exists($activity, 'task'), 'task() relationship method should exist');
        $this->assertTrue(method_exists($activity, 'user'), 'user() relationship method should exist');
    }

    #[Test]
    public function activity_causer_and_source_are_different_relationships()
    {
        /** Arrange */
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        /** Act */
        $causerRelationship = $activity->causer();
        $sourceRelationship = $activity->source();

        /** Assert */
        $this->assertInstanceOf(MorphTo::class, $causerRelationship);
        $this->assertInstanceOf(MorphTo::class, $sourceRelationship);
        $this->assertNotSame($causerRelationship, $sourceRelationship);
    }

    #[Test]
    public function activity_with_different_causer_resolves_correctly()
    {
        /** Arrange */
        $anotherUser = User::factory()->create();
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $anotherUser->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        /** Act */
        $causer = $activity->causer;

        /** Assert */
        $this->assertNotNull($causer);
        $this->assertEquals($anotherUser->id, $causer->id);
        $this->assertNotEquals($this->user->id, $causer->id);
    }

    #[Test]
    public function activity_with_null_causer_returns_null()
    {
        /** Arrange */
        $activity = Activity::create([
            'causer_type' => null,
            'causer_id' => null,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity without causer',
        ]);

        /** Act */
        $causer = $activity->causer;

        /** Assert */
        $this->assertNull($causer);
    }

    //endregion
}