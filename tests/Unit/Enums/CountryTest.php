<?php

namespace Tests\Unit\Enums;

use App\Enums\Country;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function from_code_returns_correct_country_instance()
    {
        $country = Country::fromCode('DK');
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('DK', $country->getCode());
    }

    /** @test */
    public function from_code_returns_correct_display_value()
    {
        $country = Country::fromCode('DK');
        $this->assertEquals('Denmark', $country->getDisplayValue());
    }

    /** @test */
    public function from_code_returns_correct_currency_code()
    {
        $country = Country::fromCode('DK');
        $this->assertEquals('DKK', $country->getCurrencyCode());
    }

    /** @test */
    public function from_code_returns_correct_language()
    {
        $country = Country::fromCode('DK');
        $this->assertEquals('Danish', $country->getLanguage());
    }

    /** @test */
    public function from_code_returns_correct_phone_code()
    {
        $country = Country::fromCode('DK');
        $this->assertEquals('+45', $country->getPhoneCode());
    }

    /** @test */
    public function from_code_returns_format_array()
    {
        $country = Country::fromCode('DK');
        $format = $country->getFormat();
        $this->assertIsArray($format);
        $this->assertArrayHasKey('carbonDate', $format);
        $this->assertArrayHasKey('carbonTime', $format);
        $this->assertArrayHasKey('frontendDate', $format);
    }

    /** @test */
    public function from_code_returns_fallback_to_other_for_unknown_code()
    {
        $country = Country::fromCode('XX');
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('OT', $country->getCode());
        $this->assertEquals('Other', $country->getDisplayValue());
    }

    /** @test */
    public function values_returns_all_ten_countries()
    {
        $values = Country::values();
        $this->assertCount(10, $values);
    }

    /** @test */
    public function values_contains_expected_country_codes()
    {
        $values = Country::values();
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

    /** @test */
    public function us_country_has_different_date_format()
    {
        $us = Country::fromCode('US');
        $format = $us->getFormat();
        $this->assertEquals('mm/dd/yyyy', $format['frontendDate']);
        $this->assertEquals('m/d/Y', $format['carbonDate']);
    }

    /** @test */
    public function from_code_returns_germany_correctly()
    {
        $country = Country::fromCode('DE');
        $this->assertEquals('Germany', $country->getDisplayValue());
        $this->assertEquals('EUR', $country->getCurrencyCode());
        $this->assertEquals('German', $country->getLanguage());
    }

    /** @test */
    public function from_code_returns_united_kingdom_correctly()
    {
        $country = Country::fromCode('GB');
        $this->assertEquals('United Kingdom', $country->getDisplayValue());
        $this->assertEquals('+44', $country->getPhoneCode());
    }

    /** @test */
    public function other_country_fallback_is_returned_for_empty_string()
    {
        $country = Country::fromCode('');
        $this->assertEquals('OT', $country->getCode());
    }

    /** @test */
    public function country_constructor_sets_all_properties()
    {
        $country = new Country('TEST', [
            'displayValue' => 'Test Country',
            'currencyCode' => 'TST',
            'language' => 'Testish',
            'phoneCode' => '+999',
            'format' => ['frontendDate' => 'dd/mm/yyyy'],
        ]);

        $this->assertEquals('TEST', $country->getCode());
        $this->assertEquals('Test Country', $country->getDisplayValue());
        $this->assertEquals('TST', $country->getCurrencyCode());
        $this->assertEquals('Testish', $country->getLanguage());
        $this->assertEquals('+999', $country->getPhoneCode());
        $this->assertEquals(['frontendDate' => 'dd/mm/yyyy'], $country->getFormat());
    }
}
