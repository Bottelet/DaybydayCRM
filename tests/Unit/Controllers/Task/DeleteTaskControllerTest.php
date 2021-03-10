<?php
namespace Tests\Unit\Controllers\Task;

use Tests\TestCase;
use App\Models\Task;
use App\Models\Client;
use App\Models\Invoice;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeleteTaskControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $task;

    public function setUp(): void
    {
        parent::setUp();

        $this->task = factory(Task::class)->create();
        
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @test */
    public function deleteTask()
    {
        $this->json('DELETE', route('tasks.destroy', $this->task->external_id));
        
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }
}
