<?php

namespace Tests\Unit\Status;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TypeOfStatusAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $task;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    #[Group('junie_repaired')]
    public function happy_path()
    {
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

        $this->assertNotNull(Status::typeOfTask()->get()->where('title', 'Hello'));
        $this->assertNotNull(Status::typeOfLead()->get()->where('title', 'Hello'));
        $this->assertNotNull(Status::typeOfProject()->get()->where('title', 'Hello'));
    }
}
