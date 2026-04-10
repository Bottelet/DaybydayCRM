<?php

namespace Tests\Unit\Controllers\Client;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Industry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientsControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    #[Test]
    public function can_create_client()
    {
        $this->markTestIncomplete('This test is skipped because it is not working');
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
            'industry_id' => Industry::first()->id,
            'user_id' => User::first()->id,
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
        $client = factory(Client::class)->create();

        $this->assertNotNull(Client::where('external_id', $client->external_id)->first());
        $r = $this->json('delete', route('clients.destroy', $client->external_id));

        $this->assertNull(Client::where('external_id', $client->external_id)->first());
    }

    #[Test]
    public function can_update_client()
    {
        // Create authenticated user with client-update permission
        $authUser = factory(User::class)->create();
        $role = Role::where('name', 'employee')->first();
        $authUser->attachRole($role);
        $updatePermission = Permission::firstOrCreate(['name' => 'client-update']);
        $authUser->roles->first()->attachPermission($updatePermission);
        
        // Clear permission cache
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        
        $this->actingAs($authUser);

        // Create required dependencies
        $industry = factory(Industry::class)->create();
        $user = factory(User::class)->create();

        $client = factory(Client::class)->create(
            [
                'vat' => '5898989898',
                'company_type' => 'A/S',
                'company_name' => 'Hello',
                'industry_id' => $industry->id,
                'user_id' => $user->id,
            ]
        );

        $contact = factory(Contact::class)->create(
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
        $authUser = factory(User::class)->create();
        $role = Role::where('name', 'employee')->first();
        $authUser->attachRole($role);
        $updatePermission = Permission::firstOrCreate(['name' => 'client-update']);
        $authUser->roles->first()->attachPermission($updatePermission);
        
        // Clear permission cache
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        
        $this->actingAs($authUser);

        $client = factory(Client::class)->create();
        $user = factory(User::class)->create();

        $this->assertNotEquals($client->user_id, $user->id);

        $r = $this->json('POST', '/clients/updateassign/'.$client->external_id, [
            'user_external_id' => $user->external_id,
        ]);

        $this->assertEquals($client->refresh()->user_id, $user->id);
    }

    #[Test]
    public function cant_update_assignee_without_permission()
    {
        $client = factory(Client::class)->create();
        $user = factory(User::class)->create();
        $this->setUser($user);
        $this->assertNotEquals($client->user_id, $user->id);

        $response = $this->json('POST', '/clients/updateassign/'.$client->external_id, [
            'user_external_id' => $user->external_id,
        ]);

        $response->assertStatus(302);

        $response->assertSessionHas('flash_message_warning');

        $this->assertNotEquals($client->refresh()->user_id, $user->id);
    }

    #[Test]
    public function can_update_client_without_primary_contact()
    {
        // Create authenticated user with client-update permission
        $authUser = factory(User::class)->create();
        $role = Role::where('name', 'employee')->first();
        $authUser->attachRole($role);
        $updatePermission = Permission::firstOrCreate(['name' => 'client-update']);
        $authUser->roles->first()->attachPermission($updatePermission);
        
        // Clear permission cache
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        
        $this->actingAs($authUser);

        // Create test data instead of relying on seeded data
        $industry = factory(Industry::class)->create();
        $user = factory(User::class)->create();

        $client = factory(Client::class)->create([
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
