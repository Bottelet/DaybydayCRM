<?php

namespace Tests\Unit\Controllers\Client;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Industry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Enums\PermissionName;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_create_client()
    {
        // Create authenticated user with client-create permission
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_CREATE);

        $industry = Industry::factory()->create();
        $user = User::factory()->create();

        $response = $this->json('POST', route('clients.store'), [
            'name' => 'James Test',
            'email' => 'james@test.com',
            'primary_number' => '2342342342',
            'secondary_number' => '423423432',
            'vat' => '12312334',
            'company_name' => 'James & Co',
            'address' => 'james street',
            'zipcode' => '2222',
            'city' => 'Bond city',
            'company_type' => 'Aps',
            'industry_id' => $industry->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals(302, $response->getStatusCode());

        $client = Client::where('vat', '12312334')->first();
        $contacts = $client->contacts()->get();

        $this->assertCount(1, $contacts);
        $this->assertNotNull($client);
        $this->assertNotNull($client->contacts);
    }

    #[Test]
    public function can_delete_without_any_relations_client()
    {
        // Create authenticated user with client-delete permission
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_DELETE);

        $client = Client::factory()->create();

        $this->assertNotNull(Client::where('external_id', $client->external_id)->first());
        $r = $this->json('delete', route('clients.destroy', $client->external_id));

        $this->assertSoftDeleted($client);
    }

    #[Test]
    public function can_update_client()
    {
        // Create authenticated user with client-update permission
        $this->user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $this->user->attachRole($role);
        $this->withPermissions(PermissionName::CLIENT_UPDATE);

        // Create required dependencies
        $industry = Industry::factory()->create();
        $user = User::factory()->create();

        $client = Client::factory()->create(
            [
                'vat' => '5898989898',
                'company_type' => 'A/S',
                'company_name' => 'Hello',
                'industry_id' => $industry->id,
                'user_id' => $user->id,
            ]
        );

        $contact = Contact::factory()->create(
            [
                'name' => 'Kristian',
                'secondary_number' => '11111111',
                'primary_number' => '2342342342',
                'client_id' => $client->id,
                'is_primary' => true,
            ]
        );

        $response = $this->json('PATCH', route('clients.update', $client->external_id), [
            'name' => 'Mads',
            'email' => 'james@test.com',
            'primary_number' => '2342342342',
            'secondary_number' => '423423432',
            'vat' => '12312335',
            'company_name' => 'Hello',
            'address' => 'mads street',
            'zipcode' => '2222',
            'city' => 'Bond city',
            'company_type' => 'Aps',
            'industry_id' => $industry->id,
            'user_id' => $user->id,
        ]);

        // Verify the update succeeded
        $response->assertStatus(302);

        $client = Client::where('vat', '12312335')->first();
        $this->assertNotNull($client, 'Client should exist with updated VAT number 12312335');
        $this->assertEquals($client->vat, '12312335');
        $this->assertEquals($client->company_type, 'Aps');
        $this->assertEquals($client->company_name, 'Hello');

        $this->assertEquals($client->primaryContact->primary_number, '2342342342');
        $this->assertEquals($client->primaryContact->secondary_number, '423423432');
        $this->assertEquals($client->primaryContact->name, 'Mads');

        $this->assertNull(Client::where('vat', '5898989898')->first());
    }

    #[Test]
    public function can_update_assignee()
    {
        // Create authenticated user with client-update permission
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_UPDATE);

        // Create initial user for the client
        $initialUser = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $initialUser->id]);

        // Create target user to assign
        $targetUser = User::factory()->create();

        $this->assertEquals($client->user_id, $initialUser->id);
        $this->assertNotEquals($client->user_id, $targetUser->id);

        $r = $this->json('POST', '/clients/updateassign/'.$client->external_id, [
            'user_external_id' => $targetUser->external_id,
        ]);

        // Verify the update succeeded
        $r->assertStatus(302);
        $r->assertSessionHas('flash_message');

        $this->assertEquals($client->refresh()->user_id, $targetUser->id);
    }

    #[Test]
    public function cant_update_assignee_without_permission()
    {
        $client = Client::factory()->create();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->assertNotEquals($client->user_id, $this->user->id);

        $response = $this->json('POST', '/clients/updateassign/'.$client->external_id, [
            'user_external_id' => $this->user->external_id,
        ]);

        $response->assertStatus(302);

        $response->assertSessionHas('flash_message_warning');

        $this->assertNotEquals($client->refresh()->user_id, $this->user->id);
    }

    #[Test]
    public function can_update_client_without_primary_contact()
    {
        // Create authenticated user with client-update permission
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_UPDATE);

        // Create test data instead of relying on seeded data
        $industry = Industry::factory()->create();
        $user = User::factory()->create();

        $client = Client::factory()->create([
            'vat' => '9999999999',
            'company_type' => 'A/S',
            'company_name' => 'NoPrimary Co',
        ]);

        // The ClientFactory afterCreating hook creates a primary contact.
        // Delete all contacts so the client has no primary contact for this test.
        $client->contacts()->forceDelete();

        $response = $this->json('PATCH', route('clients.update', $client->external_id), [
            'name' => 'No Contact Name',
            'email' => 'noprimary@test.com',
            'primary_number' => '1234567890',
            'secondary_number' => '0987654321',
            'vat' => '8888888888',
            'company_name' => 'NoPrimary Co Updated',
            'address' => 'no contact street',
            'zipcode' => '1111',
            'city' => 'Null City',
            'company_type' => 'ApS',
            'industry_id' => $industry->id,
            'user_id' => $user->id,
        ]);

        // Should succeed and redirect, not crash with null property access
        $response->assertStatus(302);
        $response->assertSessionHas('flash_message');

        $updatedClient = Client::where('vat', '8888888888')->first();
        $this->assertNotNull($updatedClient);
        $this->assertEquals('NoPrimary Co Updated', $updatedClient->company_name);
        $this->assertNull($updatedClient->primaryContact);
    }
}
