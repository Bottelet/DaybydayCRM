<?php

namespace Tests\Unit\Enums;

use App\Enums\Country;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CountryTest extends AbstractTestCase
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

    # region happy_path

    #[Test]
    public function it_from_code_returns_correct_country_instance()
    {
        /** Arrange */
        $countryCode = 'DK';

        /** Act */
        $country = Country::fromCode($countryCode);

        /** Assert */
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('DK', $country->getCode());
    }

    #[Test]
    public function it_from_code_returns_correct_display_value()
    {
        /** Arrange */
        $countryCode = 'DK';

        /** Act */
        $country = Country::fromCode($countryCode);

        /** Assert */
        $this->assertEquals('Denmark', $country->getDisplayValue());
    }

    #[Test]
    public function it_from_code_returns_correct_currency_code()
    {
        /** Arrange */
        $countryCode = 'DK';

        /** Act */
        $country = Country::fromCode($countryCode);

        /** Assert */
        $this->assertEquals('DKK', $country->getCurrencyCode());
    }

    #[Test]
    public function it_from_code_returns_correct_language()
    {
        /** Arrange */
        $countryCode = 'DK';

        /** Act */
        $country = Country::fromCode($countryCode);

        /** Assert */
        $this->assertEquals('Danish', $country->getLanguage());
    }

    #[Test]
    public function it_from_code_returns_correct_phone_code()
    {
        /** Arrange */
        $countryCode = 'DK';

        /** Act */
        $country = Country::fromCode($countryCode);

        /** Assert */
        $this->assertEquals('+45', $country->getPhoneCode());
    }

    #[Test]
    public function it_from_code_returns_format_array()
    {
        /** Arrange */
        $countryCode = 'DK';

        /** Act */
        $country = Country::fromCode($countryCode);
        $format = $country->getFormat();

        /** Assert */
        $this->assertIsArray($format);
        $this->assertArrayHasKey('carbonDate', $format);
        $this->assertArrayHasKey('carbonTime', $format);
        $this->assertArrayHasKey('frontendDate', $format);
    }

    #[Test]
    public function it_from_code_returns_germany_correctly()
    {
        /** Arrange */
        $countryCode = 'DE';

        /** Act */
        $country = Country::fromCode($countryCode);

        /** Assert */
        $this->assertEquals('Germany', $country->getDisplayValue());
        $this->assertEquals('EUR', $country->getCurrencyCode());
        $this->assertEquals('German', $country->getLanguage());
    }

    #[Test]
    public function it_from_code_returns_united_kingdom_correctly()
    {
        /** Arrange */
        $countryCode = 'GB';

        /** Act */
        $country = Country::fromCode($countryCode);

        /** Assert */
        $this->assertEquals('United Kingdom', $country->getDisplayValue());
        $this->assertEquals('+44', $country->getPhoneCode());
    }

    #[Test]
    public function it_from_code_returns_sweden_correctly()
    {
        /** Arrange */
        $countryCode = 'SE';

        /** Act */
        $country = Country::fromCode($countryCode);

        /** Assert */
        $this->assertEquals('Sweden', $country->getDisplayValue());
        $this->assertEquals('SEK', $country->getCurrencyCode());
        $this->assertEquals('Swedish', $country->getLanguage());
    }

    #[Test]
    public function it_us_country_has_different_date_format()
    {
        /** Arrange */
        $countryCode = 'US';

        /** Act */
        $us = Country::fromCode($countryCode);
        $format = $us->getFormat();

        /** Assert */
        $this->assertEquals('mm/dd/yyyy', $format['frontendDate']);
        $this->assertEquals('m/d/Y', $format['carbonDate']);
    }

    #[Test]
    public function it_from_code_returns_us_with_different_carbon_date_format()
    {
        /** Arrange */
        $countryCode = 'US';

        /** Act */
        $us = Country::fromCode($countryCode);
        $format = $us->getFormat();

        /** Assert */
        $this->assertEquals('m/d/Y', $format['carbonDate']);
        $this->assertEquals('g:i A', $format['carbonTime']);
    }

    #[Test]
    public function it_values_returns_all_ten_countries()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $values = Country::values();

        /** Assert */
        $this->assertCount(10, $values);
    }

    #[Test]
    public function it_values_contains_expected_country_codes()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $values = Country::values();

        /** Assert */
        $this->assertArrayHasKey('DK', $values);
        $this->assertArrayHasKey('DE', $values);
        $this->assertArrayHasKey('SE', $values);
        $this->assertArrayHasKey('GB', $values);
        $this->assertArrayHasKey('FR', $values);
        $this->assertArrayHasKey('US', $values);
        $this->assertArrayHasKey('RU', $values);
        $this->assertArrayHasKey('ES', $values);
        $this->assertArrayHasKey('IN', $values);
        $this->assertArrayHasKey('OT', $values);
    }

    #[Test]
    public function it_country_constructor_sets_all_properties()
    {
        /** Arrange */
        $code = 'TEST';
        $properties = [
            'displayValue' => 'Test Country',
            'currencyCode' => 'TST',
            'language' => 'Testish',
            'phoneCode' => '+999',
            'format' => ['frontendDate' => 'dd/mm/yyyy'],
        ];

        /** Act */
        $country = new Country($code, $properties);

        /** Assert */
        $this->assertEquals('TEST', $country->getCode());
        $this->assertEquals('Test Country', $country->getDisplayValue());
        $this->assertEquals('TST', $country->getCurrencyCode());
        $this->assertEquals('Testish', $country->getLanguage());
        $this->assertEquals('+999', $country->getPhoneCode());
        $this->assertEquals(['frontendDate' => 'dd/mm/yyyy'], $country->getFormat());
    }

    #[Test]
    public function it_from_code_returns_ot_directly_when_ot_is_requested()
    {
        /** Arrange */
        $countryCode = 'OT';

        /** Act */
        $country = Country::fromCode($countryCode);

        /** Assert */
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('OT', $country->getCode());
        $this->assertEquals('Other', $country->getDisplayValue());
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_from_code_returns_fallback_to_other_for_unknown_code()
    {
        /** Arrange */
        $unknownCode = 'XX';

        /** Act */
        $country = Country::fromCode($unknownCode);

        /** Assert */
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('OT', $country->getCode());
        $this->assertEquals('Other', $country->getDisplayValue());
    }

    #[Test]
    public function it_other_country_fallback_is_returned_for_empty_string()
    {
        /** Arrange */
        $emptyCode = '';

        /** Act */
        $country = Country::fromCode($emptyCode);

        /** Assert */
        $this->assertEquals('OT', $country->getCode());
    }

    #[Test]
    public function it_from_code_fallback_ot_has_expected_properties()
    {
        /** Arrange */
        $unknownCode = 'ZZ';

        /** Act */
        $country = Country::fromCode($unknownCode);

        /** Assert */
        $this->assertEquals('EUR', $country->getCurrencyCode());
        $this->assertEquals('English', $country->getLanguage());
        $this->assertEquals('+44', $country->getPhoneCode());
    }

    # endregion
}
