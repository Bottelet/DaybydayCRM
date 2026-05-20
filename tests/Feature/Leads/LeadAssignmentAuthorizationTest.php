<?php

namespace Tests\Feature\Leads;

use App\Enums\PermissionName;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('assignment_authorization')]
class LeadAssignmentAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $authorizedUser;

    private User $unauthorizedUser;

    private User $newAssignee;

    private Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        /* Arrange */
        $this->authorizedUser   = User::factory()->create();
        $this->unauthorizedUser = User::factory()->create();
        $this->newAssignee      = User::factory()->create();

        $client     = Client::factory()->create();
        $this->lead = Lead::factory()->create([
            'user_assigned_id' => $this->authorizedUser->id,
            'client_id'        => $client->id,
        ]);
    }

    #[Test]
    public function it_authorized_user_can_reassign_lead()
    {
        /* Arrange */
        $originalAssignee = $this->lead->user_assigned_id;
        $this->user       = $this->authorizedUser;
        $this->withPermissions(PermissionName::LEAD_ASSIGN);
        $this->user = $this->user->fresh();
        $this->assertTrue($this->user->can('can-assign-new-user-to-lead'));
        $this->assertEquals($this->user->id, $originalAssignee);

        /* Act */
        $response = $this->actingAs($this->user)
            ->patch(route('leads.updateAssign', $this->lead->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message');
        $this->assertDatabaseHas('leads', [
            'id'               => $this->lead->id,
            'user_assigned_id' => $this->newAssignee->id,
        ]);
        $this->assertEquals($this->newAssignee->id, $this->lead->refresh()->user_assigned_id);
    }

    #[Test]
    public function it_unauthorized_user_cannot_reassign_lead()
    {
        /* Arrange */
        $originalAssignee = $this->lead->user_assigned_id;
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->unauthorizedUser = $this->unauthorizedUser->fresh();
        $this->assertFalse($this->unauthorizedUser->can('can-assign-new-user-to-lead'));

        /* Act */
        $response = $this->actingAs($this->unauthorizedUser)
            ->patch(route('leads.updateAssign', $this->lead->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        /* Assert */
        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');
        $this->assertDatabaseHas('leads', [
            'id'               => $this->lead->id,
            'user_assigned_id' => $originalAssignee,
        ]);
        $this->assertEquals($originalAssignee, $this->lead->refresh()->user_assigned_id);
    }
}
