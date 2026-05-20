<?php

namespace Tests\Feature\Projects;

use App\Enums\PermissionName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('button-visibility')]
class ProjectIndexButtonVisibilityTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_new_project_button_when_user_has_project_create_permission()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::PROJECT_CREATE);

        /* Act */
        $response = $this->get(route('projects.index'));

        /* Assert */
        $response->assertOk();
        $response->assertSee(
            '<a href="'.route('projects.create').'" class="btn btn-brand cta-btn pull-right">New Project</a>',
            false
        );
    }

    #[Test]
    public function it_does_not_show_new_project_button_when_user_lacks_project_create_permission()
    {
        /* Arrange */
        $unprivilegedUser = User::factory()->withRole('employee')->create();
        $this->actingAs($unprivilegedUser);

        /* Act */
        $response = $this->get(route('projects.index'));

        /* Assert */
        $response->assertOk();
        $response->assertDontSee(
            '<a href="'.route('projects.create').'" class="btn btn-brand cta-btn pull-right">New Project</a>',
            false
        );
    }
}
