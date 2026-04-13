<?php

namespace Tests\Feature\Controllers\Client;

use App\Enums\PermissionName;
use App\Http\Controllers\ClientsController;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Industry;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(ClientsController::class)]
class ClientsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region crud

    #[Test]
    public function it_can_create_client()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_CREATE);

        Setting::firstOrCreate(
            ['id' => 1],
            [
                'client_number'  => 10000,
                'invoice_number' => 10000,
                'country'        => 'US',
                'company'        => 'Test Company',
                'max_users'      => 10,
                'vat'            => 0,
                'currency'       => 'USD',
                'language'       => 'en',
            ]
        );

        $industry = Industry::factory()->create();
        $user     = User::factory()->create();

        /** Act */
        $response = $this->json('POST', route('clients.store'), [
            'name'             => 'James Test',
            'email'            => 'james@test.com',
            'primary_number'   => '2342342342',
            'secondary_number' => '423423432',
            'vat'              => '12312334',
            'company_name'     => 'James & Co',
            'address'          => 'james street',
            'zipcode'          => '2222',
            'city'             => 'Bond city',
            'company_type'     => 'Aps',
            'industry_id'      => $industry->id,
            'user_id'          => $user->id,
        ]);

        /* Assert */
        $this->assertEquals(201, $response->getStatusCode());
        $client   = Client::where('vat', '12312334')->first();
        $contacts = $client->contacts()->get();
        $this->assertCount(1, $contacts);
        $this->assertNotNull($client);
        $this->assertNotNull($client->contacts);
    }

    #[Test]
    public function it_can_delete_without_any_relations_client()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_DELETE);
        $client = Client::factory()->create();

        /* Act */
        $this->assertNotNull(Client::where('external_id', $client->external_id)->first());
        $r = $this->json('delete', route('clients.destroy', $client->external_id));

        /* Assert */
        $this->assertSoftDeleted($client);
    }

    #[Test]
    public function it_can_update_client()
    {
        /* Arrange */
        $this->user = User::factory()->create();
        $role       = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $this->user->attachRole($role);
        $this->withPermissions(PermissionName::CLIENT_UPDATE);

        $industry = Industry::factory()->create();
        $user     = User::factory()->create();

        $client = Client::factory()->create([
            'vat'          => '5898989898',
            'company_type' => 'A/S',
            'company_name' => 'Hello',
            'industry_id'  => $industry->id,
            'user_id'      => $user->id,
        ]);

        $contact = Contact::factory()->create([
            'name'             => 'Kristian',
            'secondary_number' => '11111111',
            'primary_number'   => '2342342342',
            'client_id'        => $client->id,
            'is_primary'       => true,
        ]);

        /** Act */
        $response = $this->json('PATCH', route('clients.update', $client->external_id), [
            'name'             => 'Mads',
            'email'            => 'james@test.com',
            'primary_number'   => '2342342342',
            'secondary_number' => '423423432',
            'vat'              => '12312335',
            'company_name'     => 'Hello',
            'address'          => 'mads street',
            'zipcode'          => '2222',
            'city'             => 'Bond city',
            'company_type'     => 'Aps',
            'industry_id'      => $industry->id,
            'user_id'          => $user->id,
        ]);

        /* Assert */
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
    public function it_can_update_assignee()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_UPDATE);
        $initialUser = User::factory()->create();
        $client      = Client::factory()->create(['user_id' => $initialUser->id]);
        $targetUser  = User::factory()->create();

        $this->assertEquals($client->user_id, $initialUser->id);
        $this->assertNotEquals($client->user_id, $targetUser->id);

        /** Act */
        $r = $this->json('POST', '/clients/updateassign/' . $client->external_id, [
            'user_external_id' => $targetUser->external_id,
        ]);

        /* Assert */
        $r->assertStatus(302);
        $r->assertSessionHas('flash_message');
        $this->assertEquals($client->refresh()->user_id, $targetUser->id);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_can_update_client_without_primary_contact()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::CLIENT_UPDATE);

        $industry = Industry::factory()->create();
        $user     = User::factory()->create();

        $client = Client::factory()->create([
            'vat'          => '9999999999',
            'company_type' => 'A/S',
            'company_name' => 'NoPrimary Co',
        ]);

        $client->contacts()->forceDelete();

        /** Act */
        $response = $this->json('PATCH', route('clients.update', $client->external_id), [
            'name'             => 'No Contact Name',
            'email'            => 'noprimary@test.com',
            'primary_number'   => '1234567890',
            'secondary_number' => '0987654321',
            'vat'              => '8888888888',
            'company_name'     => 'NoPrimary Co Updated',
            'address'          => 'no contact street',
            'zipcode'          => '1111',
            'city'             => 'Null City',
            'company_type'     => 'ApS',
            'industry_id'      => $industry->id,
            'user_id'          => $user->id,
        ]);

        /* Assert */
        $response->assertStatus(302);
        $response->assertSessionHas('flash_message');
        $updatedClient = Client::where('vat', '8888888888')->first();
        $this->assertNotNull($updatedClient);
        $this->assertEquals('NoPrimary Co Updated', $updatedClient->company_name);
        $this->assertNull($updatedClient->primaryContact);
    }

    # endregion

    # region failure_path

    #[Test]
    public function it_cant_update_assignee_without_permission()
    {
        /** Arrange */
        $client     = Client::factory()->create();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->assertNotEquals($client->user_id, $this->user->id);

        /** Act */
        $response = $this->json('POST', '/clients/updateassign/' . $client->external_id, [
            'user_external_id' => $this->user->external_id,
        ]);

        /* Assert */
        $response->assertStatus(302);
        $response->assertSessionHas('flash_message_warning');
        $this->assertNotEquals($client->refresh()->user_id, $this->user->id);
    }

    # endregion
}
