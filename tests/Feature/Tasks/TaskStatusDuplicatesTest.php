<?php

namespace Tests\Feature\Tasks;

use App\Models\Status;
use App\Models\Task;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class TaskStatusDuplicatesTest extends AbstractTestCase
{
    #[Test]
    public function it_removes_duplicate_statuses_in_controller_response(): void
    {
        /* Arrange */
        // Create duplicate statuses
        Status::factory()->create(['source_type' => Task::class, 'title' => 'Open']);
        Status::factory()->create(['source_type' => Task::class, 'title' => 'Open']);

        /* Act */
        $response = $this->get(route('tasks.create'));

        /* Assert */
        $statuses = $response->viewData('statuses');
        // This will now have only 1 'Open' status
        $this->assertEquals(1, $statuses->filter(fn ($title) => $title === 'Open')->count());
    }
}
