<?php

namespace Tests\Feature\Clients;

use App\Enums\PermissionName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('button-visibility')]
class ClientIndexButtonVisibilityTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_new_client_button_when_user_has_client_create_permission()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::CLIENT_CREATE);

        /* Act */
        $response = $this->get(route('clients.index'));

        /* Assert */
        $response->assertOk();
        $response->assertSee(
            '<a href="'.route('clients.create').'" class="btn btn-brand cta-btn pull-right">New Client</a>',
            false
        );
    }

    #[Test]
    public function it_does_not_show_new_client_button_when_user_lacks_client_create_permission()
    {
        /* Arrange */
        $unprivilegedUser = User::factory()->withRole('employee')->create();
        $this->actingAs($unprivilegedUser);

        /* Act */
        $response = $this->get(route('clients.index'));

        /* Assert */
        $response->assertOk();
        $response->assertDontSee(
            '<a href="'.route('clients.create').'" class="btn btn-brand cta-btn pull-right">New Client</a>',
            false
        );
    }
}
