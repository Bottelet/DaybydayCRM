<?php

namespace Tests\Feature\Tasks;

use App\Enums\PermissionName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('button-visibility')]
class TaskIndexButtonVisibilityTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_new_task_button_when_user_has_task_create_permission()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::TASK_CREATE);

        /* Act */
        $response = $this->get(route('tasks.index'));

        /* Assert */
        $response->assertOk();
        $response->assertSee(
            '<a href="'.route('tasks.create').'" class="btn btn-brand cta-btn pull-right">New Task</a>',
            false
        );
    }

    #[Test]
    public function it_does_not_show_new_task_button_when_user_lacks_task_create_permission()
    {
        /* Arrange */
        $unprivilegedUser = User::factory()->withRole('employee')->create();
        $this->actingAs($unprivilegedUser);

        /* Act */
        $response = $this->get(route('tasks.index'));

        /* Assert */
        $response->assertOk();
        $response->assertDontSee(
            '<a href="'.route('tasks.create').'" class="btn btn-brand cta-btn pull-right">New Task</a>',
            false
        );
    }
}
