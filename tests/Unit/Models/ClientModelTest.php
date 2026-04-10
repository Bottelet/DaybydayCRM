<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Contact;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function get_primary_contact_attribute_returns_null_when_no_contacts_exist()
    {
        $client = Client::factory()->create();
        // Remove any contacts created by the factory afterCreating hook
        $client->contacts()->forceDelete();

        $this->assertNull($client->primaryContact);
    }

    #[Test]
    public function get_primary_contact_attribute_returns_null_when_no_primary_contact()
    {
        $client = Client::factory()->create();
        // Remove contacts created by factory, then add one that is NOT primary
        $client->contacts()->forceDelete();

        Contact::factory()->create([
            'client_id' => $client->id,
            'is_primary' => false,
        ]);

        $this->assertNull($client->primaryContact);
    }

    #[Test]
    public function get_primary_contact_attribute_returns_primary_contact_when_one_exists()
    {
        $client = Client::factory()->create();
        $client->contacts()->forceDelete();

        $primaryContact = Contact::factory()->create([
            'client_id' => $client->id,
            'is_primary' => true,
        ]);

        $result = $client->primaryContact;

        $this->assertNotNull($result);
        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($primaryContact->id, $result->id);
    }

    #[Test]
    public function get_primary_contact_attribute_returns_only_the_primary_contact_when_multiple_contacts_exist()
    {
        $client = Client::factory()->create();
        $client->contacts()->forceDelete();

        $primaryContact = Contact::factory()->create([
            'client_id' => $client->id,
            'is_primary' => true,
        ]);

        Contact::factory()->create([
            'client_id' => $client->id,
            'is_primary' => false,
        ]);

        $result = $client->primaryContact;

        $this->assertNotNull($result);
        $this->assertEquals($primaryContact->id, $result->id);
        $this->assertTrue((bool) $result->is_primary);
    }

    #[Test]
    public function primary_contact_magic_attribute_is_accessible_via_correct_camel_case_method_name()
    {
        $client = Client::factory()->create();
        $client->contacts()->forceDelete();

        $primaryContact = Contact::factory()->create([
            'client_id' => $client->id,
            'is_primary' => true,
        ]);

        // Verify the attribute is accessible via `$client->primaryContact`
        // (tests the getPrimaryContactAttribute method name capitalization fix)
        $this->assertEquals($primaryContact->id, $client->primaryContact->id);

        // Also verify via fresh load from DB to ensure it works consistently
        $freshClient = Client::find($client->id);
        $this->assertEquals($primaryContact->id, $freshClient->primaryContact->id);
    }

    #[Test]
    public function get_primary_contact_attribute_returns_null_on_fresh_client_without_contacts()
    {
        // Regression: before the fix, calling ->primaryContact on a client without
        // contacts would throw "Attempt to read property on null"
        $client = Client::factory()->create();
        $client->contacts()->forceDelete();

        // Should return null, not throw an exception
        $result = $client->fresh()->primaryContact;
        $this->assertNull($result);
    }
}
