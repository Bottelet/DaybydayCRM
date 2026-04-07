<?php

namespace Tests\Unit\Controllers\Task;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteTaskControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = factory(Task::class)->create();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function delete_task()
    {
        $this->json('DELETE', route('tasks.destroy', $this->task->external_id));

        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }
}
