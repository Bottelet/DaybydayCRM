<?php

namespace Tests\Unit\Models;

use App\Models\Activity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
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
    public function activity_requires_external_id_and_ip_address_when_created_directly()
    {
        $this->expectException(QueryException::class);

        Activity::create([
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'source_type' => Task::class,
            'source_id' => $this->task->id,
            'text' => 'Test activity',
        ]);
    }

    #[Test]
    public function activity_preserves_explicitly_provided_external_id_when_saved()
    {
        $customExternalId = 'custom-external-id-12345';
        $customIpAddress = '127.0.0.1';

        $activity = new Activity;
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
        $activity1 = new Activity;
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

        $activity2 = new Activity;
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
