<?php

namespace Tests\Unit\Controllers\Settings;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('authorization-fix')]
class SettingsAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;
    private User $nonAdminUser;
    private Setting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setting = Setting::first();

        // Create admin user
        $adminRole = Role::where('name', 'administrator')->orWhere('name', 'owner')->first();
        $this->adminUser = factory(User::class)->create();
        $this->adminUser->attachRole($adminRole);

        // Create non-admin user
        $employeeRole = Role::where('name', 'employee')->first();
        if (!$employeeRole) {
            $employeeRole = Role::create([
                'name' => 'employee',
                'display_name' => 'Employee',
                'description' => 'Regular employee',
            ]);
        }
        $this->nonAdminUser = factory(User::class)->create();
        $this->nonAdminUser->attachRole($employeeRole);
    }

    #[Test]
    public function admin_can_access_settings_index()
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('settings.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function non_admin_cannot_access_settings_index()
    {
        $this->actingAs($this->nonAdminUser);

        $response = $this->get(route('settings.index'));

        $response->assertStatus(302); // Redirect back
    }

    #[Test]
    public function admin_can_update_overall_settings()
    {
        $this->actingAs($this->adminUser);

        $response = $this->json('PATCH', route('settings.update'), [
            'company' => 'Test Company',
            'vat' => 25,
            'currency' => 'USD',
            'language' => 'en',
            'country' => 'US',
            'client_number' => $this->setting->client_number,
            'invoice_number' => $this->setting->invoice_number,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertStatus(302);
        $this->assertEquals('Test Company', Setting::first()->company);
    }

    #[Test]
    public function non_admin_cannot_update_overall_settings()
    {
        $this->actingAs($this->nonAdminUser);

        $originalCompany = $this->setting->company;

        $response = $this->json('PATCH', route('settings.update'), [
            'company' => 'Malicious Company',
            'vat' => 25,
            'currency' => 'USD',
            'language' => 'en',
            'country' => 'US',
            'client_number' => $this->setting->client_number,
            'invoice_number' => $this->setting->invoice_number,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertStatus(302); // Redirect back with error
        $this->assertEquals($originalCompany, Setting::first()->company);
    }

    #[Test]
    public function admin_can_update_first_step_settings()
    {
        $this->actingAs($this->adminUser);

        $response = $this->json('POST', route('settings.updateFirstStep'), [
            'company_name' => 'New Company',
            'country' => 'GB',
            'start_time' => '08:00',
            'end_time' => '18:00',
        ]);

        $response->assertStatus(302);
        $this->assertEquals('New Company', Setting::first()->company);
    }

    #[Test]
    public function non_admin_cannot_update_first_step_settings()
    {
        $this->actingAs($this->nonAdminUser);

        $originalCompany = $this->setting->company;

        $response = $this->json('POST', route('settings.updateFirstStep'), [
            'company_name' => 'Malicious Company',
            'country' => 'GB',
            'start_time' => '08:00',
            'end_time' => '18:00',
        ]);

        $response->assertStatus(302); // Redirect back with error
        $this->assertEquals($originalCompany, Setting::first()->company);
    }
}
