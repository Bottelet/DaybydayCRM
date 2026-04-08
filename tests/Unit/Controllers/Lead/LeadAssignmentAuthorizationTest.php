<?php

namespace Tests\Unit\Controllers\Lead;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('assignment_authorization')]
class LeadAssignmentAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

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
                'external_id' => \Str::uuid()->toString(),
            ]
        );

        // Create role with permission
        $authorizedRole = Role::firstOrCreate(
            ['name' => 'lead-assigner'],
            [
                'display_name' => 'Lead Assigner',
                'description' => 'Can assign leads',
                'external_id' => \Str::uuid()->toString(),
            ]
        );
        $authorizedRole->perms()->sync([$permission->id]);

        // Create authorized user
        $this->authorizedUser = factory(User::class)->create();
        $this->authorizedUser->attachRole($authorizedRole);

        // Create unauthorized user
        $this->unauthorizedUser = factory(User::class)->create();

        // Create user to assign to
        $this->newAssignee = factory(User::class)->create();

        // Create lead
        $client = factory(Client::class)->create();
        $this->lead = factory(Lead::class)->create([
            'user_assigned_id' => $this->authorizedUser->id,
            'client_id' => $client->id,
        ]);
    }

    #[Test]
    public function authorized_user_can_reassign_lead()
    {
        $originalAssignee = $this->lead->user_assigned_id;

        // Verify the authorized user has the permission
        $this->assertTrue($this->authorizedUser->can('can-assign-new-user-to-lead'));

        // Verify initial state
        $this->assertEquals($this->authorizedUser->id, $originalAssignee);

        $response = $this->actingAs($this->authorizedUser)
            ->patch(route('lead.update.assignee', $this->lead->external_id), [
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

        // Verify the unauthorized user does NOT have the permission
        $this->assertFalse($this->unauthorizedUser->can('can-assign-new-user-to-lead'));

        $response = $this->actingAs($this->unauthorizedUser)
            ->patch(route('lead.update.assignee', $this->lead->external_id), [
                'user_assigned_id' => $this->newAssignee->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_message_warning', __('You do not have permission to assign users to this lead'));

        // Verify assignment was NOT changed in database
        $this->assertDatabaseHas('leads', [
            'id' => $this->lead->id,
            'user_assigned_id' => $originalAssignee,
        ]);
        $this->assertEquals($originalAssignee, $this->lead->refresh()->user_assigned_id);
    }
}
