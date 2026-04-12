<?php

namespace Tests\Feature\Controllers\Settings;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('security')]
#[Group('settings-controller')]
class SettingsSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $nonAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a non-admin user
        $this->nonAdminUser = User::factory()->withRole('employee')->create();

        // Create and authenticate an admin user
        $this->user = User::factory()->withRole('administrator')->create();
        $this->actingAs($this->user);

        // Disable CSRF middleware for all tests
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_admin_can_access_settings_index()
    {
        $response = $this->json('GET', route('settings.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function it_non_admin_cannot_access_settings_index()
    {
        $this->actingAs($this->nonAdminUser);

        $response = $this->json('GET', route('settings.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function it_admin_can_update_overall_settings()
    {
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'company' => 'Test Company',
            'country' => 'GB',
            'language' => 'en',
            'client_number' => 1000,
            'invoice_number' => 2000,
            'currency' => 'GBP',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertRedirect();
    }

    #[Test]
    public function it_non_admin_cannot_update_overall_settings()
    {
        $this->actingAs($this->nonAdminUser);

        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'company' => 'Hacked Company',
            'country' => 'GB',
            'language' => 'en',
            'client_number' => 1000,
            'invoice_number' => 2000,
            'currency' => 'GBP',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function it_admin_can_update_first_step_settings()
    {
        $response = $this->json('POST', route('settings.updateFirstStep'), [
            'company_name' => 'Test Company',
            'country' => 'GB',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertRedirect();
    }

    #[Test]
    public function it_non_admin_cannot_update_first_step_settings()
    {
        $this->actingAs($this->nonAdminUser);

        $response = $this->json('POST', route('settings.updateFirstStep'), [
            'company_name' => 'Hacked Company',
            'country' => 'GB',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertStatus(403);
    }
}
