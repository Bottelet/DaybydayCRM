<?php

namespace Tests\Feature\Tasks;

use App\Models\Client;
use App\Models\Project;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class CreateTaskFromProjectTest extends AbstractTestCase
{
    #[Test]
    public function it_can_access_create_task_from_project_and_has_projects_populated(): void
    {
        /* Arrange */
        $client  = Client::factory()->create();
        $status  = \App\Models\Status::factory()->create(['source_type' => \App\Models\Project::class, 'title' => 'Open']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status_id' => $status->id]);

        /* Act */
        $response = $this->get(route('client.project.task.create', [$client->external_id, $project->external_id]));

        /* Assert */
        $response->assertStatus(200);
        $response->assertViewHas('projects');
        $projects = $response->viewData('projects');

        $this->assertNotNull($projects, 'Projects list is null');
        $this->assertTrue($projects->has($project->external_id), 'Project not in list: ' . $project->external_id);
    }
}
