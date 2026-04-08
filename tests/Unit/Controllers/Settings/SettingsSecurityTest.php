<?php

namespace Tests\Unit\Controllers\Settings;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('security')]
#[Group('settings-controller')]
class SettingsSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected $nonAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a non-admin user
        $this->nonAdminUser = factory(User::class)->create();
        $role = Role::where('name', 'employee')->first();
        $this->nonAdminUser->attachRole($role);
    }

    #[Test]
    public function admin_can_access_settings_index()
    {
        $response = $this->json('GET', route('settings.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function non_admin_cannot_access_settings_index()
    {
        $this->actingAs($this->nonAdminUser);

        $response = $this->json('GET', route('settings.index'));

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_update_overall_settings()
    {
        $response = $this->json('POST', route('settings.updateOverall'), [
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
    public function non_admin_cannot_update_overall_settings()
    {
        $this->actingAs($this->nonAdminUser);

        $response = $this->json('POST', route('settings.updateOverall'), [
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
    public function admin_can_update_first_step_settings()
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
    public function non_admin_cannot_update_first_step_settings()
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
