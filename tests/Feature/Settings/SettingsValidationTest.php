<?php

namespace Tests\Feature\Settings;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Verifies that UpdateSettingOverallRequest validation rules are complete
 * and that the controller returns appropriate responses for both JSON and
 * web requests.
 */
#[Group('settings-validation')]
class SettingsValidationTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-15 12:00:00');
        $this->withoutMiddleware([VerifyCsrfToken::class]);

        Setting::factory()->create([
            'client_number'  => 10000,
            'invoice_number' => 10000,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ─── Positive path ───────────────────────────────────────────────────────

    #[Test]
    public function it_accepts_valid_settings_and_returns_200_json()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'company'        => 'My Company',
            'country'        => 'GB',
            'language'       => 'en',
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'currency'       => 'GBP',
            'start_time'     => '09:00',
            'end_time'       => '17:00',
        ]);

        /* Assert */
        $response->assertOk();
        $response->assertJsonFragment(['message' => __('Overall settings successfully updated')]);
        $this->assertDatabaseHas('settings', [
            'company'        => 'My Company',
            'country'        => 'GB',
            'language'       => 'en',
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'currency'       => 'GBP',
        ]);
    }

    // ─── Validation failures ──────────────────────────────────────────────────

    #[Test]
    public function it_rejects_missing_client_number_with_422()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'company'        => 'My Company',
            'invoice_number' => 10000,
            // client_number intentionally missing
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_number']);
    }

    #[Test]
    public function it_rejects_missing_invoice_number_with_422()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'company'       => 'My Company',
            'client_number' => 10000,
            // invoice_number intentionally missing
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['invoice_number']);
    }

    #[Test]
    public function it_rejects_non_integer_client_number()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 'abc',
            'invoice_number' => 10000,
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_number']);
    }

    #[Test]
    public function it_rejects_invalid_language()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'language'       => 'xx', // not in allowed list
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['language']);
    }

    #[Test]
    public function it_rejects_invalid_currency()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'currency'       => 'INVALID',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['currency']);
    }

    #[Test]
    public function it_rejects_vat_above_100_percent()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'vat'            => 101, // over 100%
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vat']);
    }

    #[Test]
    public function it_rejects_negative_vat()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'vat'            => -5,
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vat']);
    }

    #[Test]
    public function it_rejects_invalid_time_format()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'start_time'     => 'not-a-time',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_time']);
    }

    #[Test]
    public function it_rejects_country_code_longer_than_two_characters()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act – 'GBR' is 3 chars, fails size:2 rule */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'country'        => 'GBR',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['country']);
    }

    #[Test]
    public function it_rejects_single_character_country_code()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act – 'G' is 1 char, fails size:2 rule */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'country'        => 'G',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['country']);
    }

    #[Test]
    public function it_rejects_client_number_below_minimum()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act – min:1 rule */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 0,
            'invoice_number' => 10000,
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_number']);
    }

    #[Test]
    public function it_rejects_invalid_end_time_format()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act – date_format:H:i rejects free-form strings */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'end_time'       => 'not-valid',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_time']);
    }

    #[Test]
    public function it_rejects_out_of_range_clock_value_for_start_time()
    {
        /* Arrange */
        $this->asAdmin();

        /* Act – date_format:H:i rejects semantically invalid hours/minutes */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'start_time'     => '29:99',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_time']);
    }

    // ─── Authorization ───────────────────────────────────────────────────────

    #[Test]
    public function it_returns_403_json_when_non_admin_submits_settings()
    {
        /* Arrange */
        $nonAdmin = \App\Models\User::factory()->create();
        $this->actingAs($nonAdmin);

        /* Act */
        $response = $this->json('PATCH', route('settings.updateOverall'), [
            'client_number'  => 10000,
            'invoice_number' => 10000,
        ]);

        /* Assert */
        $response->assertStatus(403);
    }

    // ─── Determinism ─────────────────────────────────────────────────────────

    #[Test]
    public function it_persists_all_submitted_fields_without_silent_overrides()
    {
        /* Arrange */
        $this->asAdmin();
        $before = Setting::first();

        /* Act */
        $this->json('PATCH', route('settings.updateOverall'), [
            'company'        => 'Determinism Test Co',
            'country'        => 'US',
            'language'       => 'en',
            'client_number'  => 10001,
            'invoice_number' => 10002,
            'currency'       => 'USD',
            'start_time'     => '08:00',
            'end_time'       => '16:00',
        ]);

        $after = Setting::first();

        /* Assert – submitted values are stored, not silently replaced by defaults */
        $this->assertEquals('Determinism Test Co', $after->company);
        $this->assertEquals('US', $after->country);
        $this->assertEquals('en', $after->language);
        $this->assertEquals(10001, $after->client_number);
        $this->assertEquals(10002, $after->invoice_number);
        $this->assertEquals('USD', $after->currency);
    }
}
