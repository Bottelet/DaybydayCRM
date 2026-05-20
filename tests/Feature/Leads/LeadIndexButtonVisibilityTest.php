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
    public function it_shows_new_lead_heading_button_when_user_has_lead_create_permission(): void
    {
        $this->withPermissions(PermissionName::LEAD_CREATE);

        $response = $this->get(route('leads.index'));

        $response->assertOk();
        $response->assertSee(
            '<a href="' . route('leads.create') . '" class="btn btn-brand cta-btn pull-right">New Lead</a>',
            false
        );
    }

    #[Test]
    public function it_does_not_show_new_lead_heading_button_when_user_lacks_lead_create_permission(): void
    {
        $unprivilegedUser = User::factory()->withRole('employee')->create();
        $this->actingAs($unprivilegedUser);

        $response = $this->get(route('leads.index'));

        $response->assertOk();
        $response->assertDontSee(
            '<a href="' . route('leads.create') . '" class="btn btn-brand cta-btn pull-right">New Lead</a>',
            false
        );
    }
}
