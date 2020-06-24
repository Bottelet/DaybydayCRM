<?php
namespace Tests\Unit\Controllers\Client;

use App\Models\Contact;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\User;
use App\Models\Industry;

use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ClientsControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    /** @test **/
    public function can_create_client()
    {
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

    /** @test **/
    public function can_delete_without_any_relations_client()
    {
        $client = factory(Client::class)->create();

        $this->assertNotNull(Client::where('external_id', $client->external_id)->first());
        $r = $this->json('delete', route('clients.destroy', $client->external_id));

        $this->assertNull(Client::where('external_id', $client->external_id)->first());
    }

    /** @test **/
    public function can_update_client()
    {
        $client = factory(Client::class)->create(
            [
                'vat' => "5898989898",
                'company_type' => 'A/S',
                'company_name' => 'Hello',
            ]
        );

        $contact = factory(Contact::class)->create(
            [
                'name' => "Kristian",
                'secondary_number' => '11111111',
                'primary_number' => '2342342342',
                'client_id' => $client->id
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
                'industry_id' => Industry::first()->id,
                'user_id' => User::first()->id,
        ]);

        $client = Client::where('vat', '12312335')->first();
        $this->assertEquals($client->vat, '12312335');
        $this->assertEquals($client->company_type, 'Aps');
        $this->assertEquals($client->company_name, 'Hello');

        $this->assertEquals($client->primaryContact->primary_number, '2342342342');
        $this->assertEquals($client->primaryContact->secondary_number, '423423432');
        $this->assertEquals($client->primaryContact->name, 'Mads');

        $this->assertNull(Client::where('vat', '5898989898')->first());
    }


    /** @test **/
    public function can_update_assignee()
    {
        $client = factory(Client::class)->create();
        $user = factory(User::class)->create();

        $this->assertNotEquals($client->user_id, $user->id);

        $r = $this->json('POST', '/clients/updateassign/' . $client->external_id, [
            'user_external_id' => $user->external_id
        ]);

        $this->assertEquals($client->refresh()->user_id, $user->id);
    }


    /** @test **/
    public function cant_update_assignee_without_permission()
    {
        $client = factory(Client::class)->create();
        $user = factory(User::class)->create();
        $this->setUser($user);
        $this->assertNotEquals($client->user_id, $user->id);

        $response = $this->json('POST', '/clients/updateassign/' . $client->external_id, [
            'user_external_id' => $user->external_id
        ]);

        $response->assertStatus(302);

        $response->assertSessionHas('flash_message_warning');

        $this->assertNotEquals($client->refresh()->user_id, $user->id);
    }
}
