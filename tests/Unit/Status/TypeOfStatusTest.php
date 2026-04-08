<?php

namespace Tests\Unit\Status;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TypeOfStatusTest extends TestCase
{
    use DatabaseTransactions;

    private $task;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    #[Group('junie_repaired')]
    public function happy_path()
    {
        factory(Status::class)->create([
            'source_type' => Task::class,
            'title' => 'Hello',
        ]);
        factory(Status::class)->create([
            'source_type' => Lead::class,
            'title' => 'Hello',
        ]);

        factory(Status::class)->create([
            'source_type' => Project::class,
            'title' => 'Hello',
        ]);

        $this->assertNotNull(Status::typeOfTask()->get()->where('title', 'Hello'));
        $this->assertNotNull(Status::typeOfLead()->get()->where('title', 'Hello'));
        $this->assertNotNull(Status::typeOfProject()->get()->where('title', 'Hello'));
    }
}
