<?php

namespace Tests\Unit\Controllers\Lead;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\User;
use App\Enums\PermissionName;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        // Create users
        $this->authorizedUser = User::factory()->create();
        $this->unauthorizedUser = User::factory()->create();
        $this->newAssignee = User::factory()->create();

        // Create lead
        $client = Client::factory()->create();
        $this->lead = Lead::factory()->create([
            'user_assigned_id' => $this->authorizedUser->id,
            'client_id' => $client->id,
        ]);
    }

    #[Test]
    public function authorized_user_can_reassign_lead()
    {
        $originalAssignee = $this->lead->user_assigned_id;

        $this->user = $this->authorizedUser;
        $this->withPermissions(PermissionName::LEAD_ASSIGN);

        // Verify the authorized user has the permission
        $this->assertTrue($this->user->can('can-assign-new-user-to-lead'));

        // Verify initial state
        $this->assertEquals($this->user->id, $originalAssignee);

        $response = $this->actingAs($this->user)
            ->patch(route('leads.updateAssign', $this->lead->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message');

        // Verify assignment was updated in database
        $this->assertDatabaseHas('leads', [
            'id' => $this->lead->id,
            'user_assigned_id' => $this->newAssignee->id,
        ]);
        $this->assertEquals($this->newAssignee->id, $this->lead->refresh()->user_assigned_id);
    }

    #[Test]
    public function unauthorized_user_cannot_reassign_lead()
    {
        $originalAssignee = $this->lead->user_assigned_id;

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->unauthorizedUser = $this->unauthorizedUser->fresh();

        // Verify the unauthorized user does NOT have the permission
        $this->assertFalse($this->unauthorizedUser->can('can-assign-new-user-to-lead'));

        $response = $this->actingAs($this->unauthorizedUser)
            ->patch(route('leads.updateAssign', $this->lead->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning');

        // Verify assignment was NOT changed in database
        $this->assertDatabaseHas('leads', [
            'id' => $this->lead->id,
            'user_assigned_id' => $originalAssignee,
        ]);
        $this->assertEquals($originalAssignee, $this->lead->refresh()->user_assigned_id);
    }
}
