<?php

namespace Tests\Feature\Tasks;

use App\Models\Status;
use App\Models\Task;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class TaskIndexStatusDuplicatesTest extends AbstractTestCase
{
    #[Test]
    public function it_removes_duplicate_statuses_in_index_response(): void
    {
        /* Arrange */
        // Create duplicate statuses
        Status::factory()->create(['source_type' => Task::class, 'title' => 'Open']);
        Status::factory()->create(['source_type' => Task::class, 'title' => 'Open']);

        /* Act */
        $response = $this->get(route('tasks.index'));

        /* Assert */
        $statuses = $response->viewData('statuses');
        // This will have 2 'Open' status if not fixed
        $this->assertEquals(1, $statuses->filter(fn ($status) => $status->title === 'Open')->count());
    }
}
