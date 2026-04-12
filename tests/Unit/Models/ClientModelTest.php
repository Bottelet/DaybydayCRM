<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class ClientModelTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region happy_path

    #[Test]
    public function get_primary_contact_attribute_returns_primary_contact_when_one_exists()
    {
        /** Arrange */
        $client = Client::factory()->create();
        $client->contacts()->forceDelete();

        $primaryContact = Contact::factory()->create([
            'client_id' => $client->id,
            'is_primary' => true,
        ]);

        /** Act */
        $result = $client->primaryContact;

        /** Assert */
        $this->assertNotNull($result);
        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($primaryContact->id, $result->id);
    }

    #[Test]
    public function get_primary_contact_attribute_returns_only_the_primary_contact_when_multiple_contacts_exist()
    {
        /** Arrange */
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

        /** Act */
        $result = $client->primaryContact;

        /** Assert */
        $this->assertNotNull($result);
        $this->assertEquals($primaryContact->id, $result->id);
        $this->assertTrue((bool) $result->is_primary);
    }

    #[Test]
    public function primary_contact_magic_attribute_is_accessible_via_correct_camel_case_method_name()
    {
        /** Arrange */
        $client = Client::factory()->create();
        $client->contacts()->forceDelete();

        $primaryContact = Contact::factory()->create([
            'client_id' => $client->id,
            'is_primary' => true,
        ]);

        /** Act */
        $result = $client->primaryContact;
        $freshClient = Client::find($client->id);
        $freshResult = $freshClient->primaryContact;

        /** Assert */
        $this->assertEquals($primaryContact->id, $result->id);
        $this->assertEquals($primaryContact->id, $freshResult->id);
    }

    // endregion

    // region edge_cases

    #[Test]
    public function get_primary_contact_attribute_returns_null_when_no_contacts_exist()
    {
        /** Arrange */
        $client = Client::factory()->create();
        $client->contacts()->forceDelete();

        /** Act */
        $result = $client->primaryContact;

        /** Assert */
        $this->assertNull($result);
    }

    #[Test]
    public function get_primary_contact_attribute_returns_null_when_no_primary_contact()
    {
        /** Arrange */
        $client = Client::factory()->create();
        $client->contacts()->forceDelete();

        Contact::factory()->create([
            'client_id' => $client->id,
            'is_primary' => false,
        ]);

        /** Act */
        $result = $client->primaryContact;

        /** Assert */
        $this->assertNull($result);
    }

    #[Test]
    public function get_primary_contact_attribute_returns_null_on_fresh_client_without_contacts()
    {
        /** Arrange */
        $client = Client::factory()->create();
        $client->contacts()->forceDelete();

        /** Act */
        $result = $client->fresh()->primaryContact;

        /** Assert */
        $this->assertNull($result);
    }

    // endregion
}
