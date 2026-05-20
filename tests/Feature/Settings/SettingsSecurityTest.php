<?php

namespace Tests\Feature\Settings;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('settings-controller')]
class SettingsSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $nonAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonAdminUser = User::factory()->withRole('employee')->create();

        $this->user = User::factory()->withRole('administrator')->create();
        $this->actingAs($this->user);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_admin_can_access_settings_index()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('GET', route('settings.index'));

        /* Assert */
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'settings',
        ]);
    }

    #[Test]
    public function it_non_admin_cannot_access_settings_index()
    {
        /* Arrange */
        $this->actingAs($this->nonAdminUser);

        /* Act */
        $response = $this->json('GET', route('settings.index'));

        /* Assert */
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
    }

    #[Test]
    public function it_admin_can_update_overall_settings()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'company'        => 'Test Company',
            'country'        => 'GB',
            'language'       => 'en',
            'client_number'  => 1000,
            'invoice_number' => 2000,
            'currency'       => 'GBP',
            'start_time'     => '09:00',
            'end_time'       => '17:00',
        ]);

        /* Assert */
        $response->assertOk();
        $this->assertDatabaseHas('settings', [
            'company'        => 'Test Company',
            'country'        => 'GB',
            'language'       => 'en',
            'client_number'  => 1000,
            'invoice_number' => 2000,
            'currency'       => 'GBP',
        ]);
    }

    #[Test]
    public function it_non_admin_cannot_update_overall_settings()
    {
        /* Arrange */
        $this->actingAs($this->nonAdminUser);

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'company'        => 'Hacked Company',
            'country'        => 'GB',
            'language'       => 'en',
            'client_number'  => 1000,
            'invoice_number' => 2000,
            'currency'       => 'GBP',
            'start_time'     => '09:00',
            'end_time'       => '17:00',
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseMissing('settings', [
            'company' => 'Hacked Company',
        ]);
    }

    #[Test]
    public function it_admin_can_update_first_step_settings()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('POST', route('settings.updateFirstStep'), [
            'company_name' => 'Test Company',
            'country'      => 'GB',
            'start_time'   => '09:00',
            'end_time'     => '17:00',
        ]);

        /* Assert */
        $response->assertRedirect();
        $this->assertDatabaseHas('settings', [
            'company' => 'Test Company',
            'country' => 'GB',
        ]);
    }

    #[Test]
    public function it_non_admin_cannot_update_first_step_settings()
    {
        /* Arrange */
        $this->actingAs($this->nonAdminUser);

        /* Act */
        $response = $this->json('POST', route('settings.updateFirstStep'), [
            'company_name' => 'Hacked Company',
            'country'      => 'GB',
            'start_time'   => '09:00',
            'end_time'     => '17:00',
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseMissing('settings', [
            'company' => 'Hacked Company',
        ]);
    }
}
