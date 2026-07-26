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
    public function it_user_with_client_delete_permission_can_delete_client(): void
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
    public function it_user_without_client_delete_permission_cannot_delete_client(): void
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
    public function it_redirects_user_without_client_create_permission_from_client_create_page(): void
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
    public function it_returns_forbidden_for_json_request_without_client_create_permission(): void
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
    public function it_prevents_user_without_client_create_permission_from_storing_client(): void
    {
        /* Arrange */
        $industry = Industry::factory()->create();
        $owner    = User::factory()->create();

        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->from(route('clients.create'))->post(route('clients.store'), [
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

        /* Assert: StoreClientRequest::authorize() failing throws AuthorizationException,
         * which the app's exception Handler now converts to a flash+redirect-back for
         * non-JSON requests (matching the rest of the app's permission-denial pattern)
         * instead of Laravel's generic 403 error page. */
        $response->assertRedirect(route('clients.create'));
        // AuthorizationException's default message ('This action is unauthorized.')
        // is a hardcoded English string, not translated via __() - StoreClientRequest
        // doesn't override failedAuthorization() to provide a custom message.
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertDatabaseMissing('clients', ['company_name' => 'James & Co']);
    }

    #[Test]
    public function it_prevents_user_without_client_update_permission_from_updating_client(): void
    {
        /* Arrange */
        $this->actingAs($this->userWithoutPermission);

        /* Act */
        $response = $this->from(route('clients.edit', $this->client->external_id))
            ->patch(route('clients.update', $this->client->external_id), [
                'name'         => 'Changed Name',
                'email'        => 'changed@test.com',
                'company_name' => 'Changed Co',
                'user_id'      => $this->userWithoutPermission->id,
            ]);

        /* Assert: UpdateClientRequest::authorize() failing throws AuthorizationException,
         * which the app's exception Handler converts to a flash+redirect-back for
         * non-JSON requests (matching the rest of the app's permission-denial pattern)
         * instead of Laravel's generic 403 error page. */
        $response->assertRedirect(route('clients.edit', $this->client->external_id));
        // AuthorizationException's default message ('This action is unauthorized.')
        // is a hardcoded English string, not translated via __() - UpdateClientRequest
        // doesn't override failedAuthorization() to provide a custom message.
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertDatabaseMissing('clients', ['company_name' => 'Changed Co']);
    }
}
