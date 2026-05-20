<?php

namespace Tests\Feature\Leads;

use App\Enums\PermissionName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('button-visibility')]
class LeadIndexButtonVisibilityTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_new_lead_button_when_user_has_lead_create_permission()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::LEAD_CREATE);

        /* Act */
        $response = $this->get(route('leads.index'));

        /* Assert */
        $response->assertOk();
        $response->assertSee(
            '<a href="'.route('leads.create').'" class="btn btn-brand cta-btn pull-right">New Lead</a>',
            false
        );
    }

    #[Test]
    public function it_does_not_show_new_lead_button_when_user_lacks_lead_create_permission()
    {
        /* Arrange */
        $unprivilegedUser = User::factory()->withRole('employee')->create();
        $this->actingAs($unprivilegedUser);

        /* Act */
        $response = $this->get(route('leads.index'));

        /* Assert */
        $response->assertOk();
        $response->assertDontSee(
            '<a href="'.route('leads.create').'" class="btn btn-brand cta-btn pull-right">New Lead</a>',
            false
        );
    }
}
