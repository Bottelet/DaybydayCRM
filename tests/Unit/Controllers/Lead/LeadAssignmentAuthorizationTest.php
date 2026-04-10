<?php

namespace Tests\Unit\Controllers\Lead;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
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

        // Create permission
        $permission = Permission::firstOrCreate(
            ['name' => 'can-assign-new-user-to-lead'],
            [
                'display_name' => 'Assign users to leads',
                'description' => 'Can assign users to leads',
                'external_id' => Str::uuid()->toString(),
            ]
        );

        // Create role with permission
        $authorizedRole = Role::firstOrCreate(
            ['name' => 'lead-assigner'],
            [
                'display_name' => 'Lead Assigner',
                'description' => 'Can assign leads',
                'external_id' => Str::uuid()->toString(),
            ]
        );
        $authorizedRole->perms()->sync([$permission->id]);

        // Create authorized user
        $this->authorizedUser = User::factory()->create();
        $this->authorizedUser->attachRole($authorizedRole);

        // Create unauthorized user
        $this->unauthorizedUser = User::factory()->create();

        // Create user to assign to
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

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        // Verify the authorized user has the permission
        $this->assertTrue($this->authorizedUser->can('can-assign-new-user-to-lead'));

        // Verify initial state
        $this->assertEquals($this->authorizedUser->id, $originalAssignee);

        $response = $this->actingAs($this->authorizedUser)
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
