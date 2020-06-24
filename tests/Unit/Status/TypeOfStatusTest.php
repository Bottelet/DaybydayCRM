<?php
namespace Tests\Unit\Status;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TypeOfStatusTest extends TestCase
{
    use DatabaseTransactions;

    private $task;

    public function setUp(): void
    {
        parent::setUp();
        factory(Status::class)->create([
            "source_type" => Task::class,
            "title" => "Hello"
        ]);
        factory(Status::class)->create([
            "source_type" => Lead::class,
            "title" => "Hello"
        ]);

        factory(Status::class)->create([
            "source_type" => Project::class,
            "title" => "Hello"
        ]);
    }

    /** @test */
    public function happyPath()
    {
        $this->assertNotNull(Status::typeOfTask()->get()->where('title', "Hello"));
        $this->assertNotNull(Status::typeOfLead()->get()->where('title', "Hello"));
        $this->assertNotNull(Status::typeOfProject()->get()->where('title', "Hello"));
    }
}
