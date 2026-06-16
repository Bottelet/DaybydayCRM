<?php

namespace Tests\Feature\Documents;

use App\Http\Controllers\DocumentsController;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(DocumentsController::class)]
class DocumentUploadModalTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create(['user_assigned_id' => $this->user->id]);
    }

    #[Test]
    public function it_can_open_upload_files_modal_for_task()
    {
        $response = $this->get('/add-documents/' . $this->task->external_id . '/task');

        $response->assertStatus(200);
    }
}
