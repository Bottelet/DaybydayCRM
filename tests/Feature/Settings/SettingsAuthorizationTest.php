<?php

namespace Tests\Feature\Settings;

use App\Models\BusinessHour;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('authorization-fix')]
class SettingsAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $nonAdminUser;

    private Setting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setting = Setting::first() ?: Setting::create([
            'company'        => 'Default Company',
            'vat'            => 25,
            'currency'       => 'USD',
            'language'       => 'en',
            'country'        => 'US',
            'client_number'  => 1,
            'invoice_number' => 1,
            'max_users'      => 10,
        ]);

        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
            BusinessHour::query()->firstOrCreate(
                ['day' => $day],
                ['open_time' => '09:00:00', 'close_time' => '17:00:00']
            );
        }

        $this->adminUser = User::factory()->withRole('administrator')->create();

        $this->nonAdminUser = User::factory()->withRole('employee')->create();
    }

    #[Test]
    public function it_admin_can_access_settings_index()
    {
        /* Arrange */
        $this->actingAs($this->adminUser);

        /* Act */
        $response = $this->get(route('settings.index'));

        /* Assert */
        $response->assertStatus(200);
    }

    #[Test]
    public function it_non_admin_cannot_access_settings_index()
    {
        /* Arrange */
        $this->actingAs($this->nonAdminUser);

        /* Act */
        $response = $this->get(route('settings.index'));

        /* Assert */
        $response->assertStatus(302);
    }

    #[Test]
    public function it_admin_can_update_overall_settings()
    {
        /* Arrange */
        $this->actingAs($this->adminUser);

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'company'        => 'Test Company',
            'vat'            => 25,
            'currency'       => 'USD',
            'language'       => 'en',
            'country'        => 'US',
            'client_number'  => $this->setting->client_number,
            'invoice_number' => $this->setting->invoice_number,
            'start_time'     => '09:00',
            'end_time'       => '17:00',
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertEquals('Test Company', Setting::first()->company);
    }

    #[Test]
    public function it_non_admin_cannot_update_overall_settings()
    {
        /* Arrange */
        $this->actingAs($this->nonAdminUser);

        $originalCompany = $this->setting->company;

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'company'        => 'Malicious Company',
            'vat'            => 25,
            'currency'       => 'USD',
            'language'       => 'en',
            'country'        => 'US',
            'client_number'  => $this->setting->client_number,
            'invoice_number' => $this->setting->invoice_number,
            'start_time'     => '09:00',
            'end_time'       => '17:00',
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertEquals($originalCompany, Setting::first()->company);
    }

    #[Test]
    public function it_admin_can_update_first_step_settings()
    {
        /* Arrange */
        $this->actingAs($this->adminUser);

        /* Act */
        $response = $this->json('POST', route('settings.updateFirstStep'), [
            'company_name' => 'New Company',
            'country'      => 'GB',
            'start_time'   => '08:00',
            'end_time'     => '18:00',
        ]);

        /* Assert */
        $response->assertStatus(302);
        $this->assertEquals('New Company', Setting::first()->company);
    }

    #[Test]
    public function it_non_admin_cannot_update_first_step_settings()
    {
        /* Arrange */
        $this->actingAs($this->nonAdminUser);

        $originalCompany = $this->setting->company;

        /* Act */
        $response = $this->json('POST', route('settings.updateFirstStep'), [
            'company_name' => 'Malicious Company',
            'country'      => 'GB',
            'start_time'   => '08:00',
            'end_time'     => '18:00',
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertEquals($originalCompany, Setting::first()->company);
    }
}
