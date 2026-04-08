<?php

namespace Tests\Unit\Models;

use App\Models\Activity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityModelBootTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->task = factory(Task::class)->create();
    }

    #[Test]
    public function activity_auto_generates_external_id_when_not_provided()
    {
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        $this->assertNotNull($activity->external_id);
        $this->assertNotEmpty($activity->external_id);
        // UUID format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $activity->external_id
        );
    }

    #[Test]
    public function activity_preserves_provided_external_id()
    {
        $customExternalId = 'custom-external-id-12345';

        $activity = Activity::create([
            'external_id' => $customExternalId,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        $this->assertEquals($customExternalId, $activity->external_id);
    }

    #[Test]
    public function activity_auto_generates_ip_address_when_not_provided()
    {
        $activity = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        $this->assertNotNull($activity->ip_address);
        $this->assertNotEmpty($activity->ip_address);
    }

    #[Test]
    public function activity_preserves_provided_ip_address()
    {
        $customIp = '192.168.1.100';

        $activity = Activity::create([
            'ip_address' => $customIp,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);

        $this->assertEquals($customIp, $activity->ip_address);
    }

    #[Test]
    public function activity_generates_unique_external_ids_for_each_record()
    {
        $activity1 = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'First activity',
        ]);

        $activity2 = Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Second activity',
        ]);

        $this->assertNotEquals($activity1->external_id, $activity2->external_id);
    }
}