<?php

namespace Tests\Unit\Models;

use App\Models\Activity;
use App\Models\Task;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityModelBootAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $user;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->task = Task::factory()->create();
    }

    #[Test]
    public function activity_auto_generates_external_id_and_ip_address_when_not_provided()
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
        $this->assertNotNull($activity->ip_address);
        $this->assertNotEmpty($activity->ip_address);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $activity->external_id
        );
    }

    #[Test]
    public function activity_preserves_explicitly_provided_external_id_when_saved()
    {
        $customExternalId = 'custom-external-id-12345';
        $customIpAddress = '127.0.0.1';

        $activity = new Activity();
        $activity->forceFill([
            'external_id' => $customExternalId,
            'ip_address' => $customIpAddress,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);
        $activity->save();

        $activity = $activity->fresh();

        $this->assertEquals($customExternalId, $activity->external_id);
        $this->assertEquals($customIpAddress, $activity->ip_address);
    }

    #[Test]
    public function activity_generates_unique_external_ids_for_each_record()
    {
        $activity1 = new Activity();
        $activity1->forceFill([
            'external_id' => Uuid::uuid4()->toString(),
            'ip_address' => '127.0.0.1',
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'First activity',
        ]);
        $activity1->save();

        $activity2 = new Activity();
        $activity2->forceFill([
            'external_id' => Uuid::uuid4()->toString(),
            'ip_address' => '127.0.0.1',
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Second activity',
        ]);
        $activity2->save();

        $this->assertNotEquals($activity1->external_id, $activity2->external_id);
    }
}
