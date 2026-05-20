<?php

namespace Tests\Feature\Clients;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\Industry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('authorization-fix')]
class ClientAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->client                = Client::factory()->create();
        $this->userWithPermission    = User::factory()->create();
        $this->userWithoutPermission = User::factory()->create();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_user_with_client_delete_permission_can_delete_client()
    {
        /* Arrange */
        $this->user = $this->userWithPermission;
        $this->withPermissions(PermissionName::CLIENT_DELETE);

        /* Act */
        $response = $this->delete(route('clients.destroy', $this->client->external_id));

        /* Assert */
        $response->assertStatus(302);
        $this->assertSoftDeleted('clients', ['id' => $this->client->id]);
    }

    #[Test]
    public function it_user_without_client_delete_permission_cannot_delete_client()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->deleteJson(route('clients.destroy', $this->client->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('clients', ['id' => $this->client->id, 'deleted_at' => null]);
    }

    #[Test]
    public function userWithoutClientCreatePermissionIsRedirectedFromClientCreatePage()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->get(route('clients.create'));

        /* Assert */
        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function jsonRequestWithoutClientCreatePermissionGetsForbiddenFromClientCreatePage()
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->getJson(route('clients.create'));

        /* Assert */
        $response
            ->assertForbidden()
            ->assertJson(['message' => __("You don't have permission to create a client")]);
    }

    #[Test]
    public function userWithoutClientCreatePermissionCannotStoreClient()
    {
        /* Arrange */
        $industry = Industry::factory()->create();
        $owner    = User::factory()->create();

        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->post(route('clients.store'), [
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
            'user_id'          => $owner->id,
        ]);

        /* Assert */
        $response->assertForbidden();
        $this->assertDatabaseMissing('clients', ['company_name' => 'James & Co']);
    }
}
