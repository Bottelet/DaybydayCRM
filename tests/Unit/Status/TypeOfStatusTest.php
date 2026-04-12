<?php

namespace Tests\Unit\Status;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class TypeOfStatusTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    // region happy_path

    #[Test]
    #[Group('junie_repaired')]
    public function type_of_status_scopes_correctly_filter_by_source_type()
    {
        /** Arrange */
        Status::factory()->create([
            'source_type' => Task::class,
            'title' => 'Hello',
        ]);
        Status::factory()->create([
            'source_type' => Lead::class,
            'title' => 'Hello',
        ]);
        Status::factory()->create([
            'source_type' => Project::class,
            'title' => 'Hello',
        ]);

        /** Act */
        $taskStatuses = Status::typeOfTask()->get()->where('title', 'Hello');
        $leadStatuses = Status::typeOfLead()->get()->where('title', 'Hello');
        $projectStatuses = Status::typeOfProject()->get()->where('title', 'Hello');

        /** Assert */
        $this->assertCount(1, $taskStatuses);
        $this->assertCount(1, $leadStatuses);
        $this->assertCount(1, $projectStatuses);
        $this->assertEquals(Task::class, $taskStatuses->first()->source_type);
        $this->assertEquals(Lead::class, $leadStatuses->first()->source_type);
        $this->assertEquals(Project::class, $projectStatuses->first()->source_type);
    }

    // endregion
}
