<?php

namespace Tests\Feature\Projects;

use App\Http\Controllers\ProjectsController;
use App\Models\Client;
use App\Models\Project;
use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(ProjectsController::class)]
class ProjectsIndexAndShowTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $client        = Client::factory()->create();
        $this->project = Project::factory()->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $this->user->id,
            'status_id'        => Status::factory()->create(['source_type' => Project::class])->id,
        ]);
    }

    #[Test]
    public function it_can_view_project_show_page()
    {
        /* Arrange */

        /* Act */
        $response = $this->get(route('projects.show', $this->project->external_id));

        /* Assert */
        $response->assertStatus(200);
        $response->assertViewIs('projects.show');
    }

    #[Test]
    public function it_returns_404_for_nonexistent_project()
    {
        /* Arrange */

        /* Act */
        $response = $this->get(route('projects.show', 'nonexistent-external-id'));

        /* Assert */
        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_view_projects_index()
    {
        /* Arrange */

        /* Act */
        $response = $this->get(route('projects.index'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertViewIs('projects.index');
    }

    #[Test]
    public function it_returns_projects_json_datatables_response()
    {
        /* Arrange */

        /* Act */
        $response = $this->get(route('projects.index.data'), [
            'Accept'          => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        /* Assert */
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    }

    #[Test]
    public function it_can_view_project_create_page()
    {
        /* Arrange */

        /* Act */
        $response = $this->get(route('projects.create'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertViewIs('projects.create');
    }
}
