<?php

namespace Tests\Unit\Enums;

use App\Enums\Country;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function fromCodeReturnsCorrectCountryInstance()
    {
        $country = Country::fromCode('DK');
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('DK', $country->getCode());
    }

    /** @test */
    public function fromCodeReturnsCorrectDisplayValue()
    {
        $country = Country::fromCode('DK');
        $this->assertEquals('Denmark', $country->getDisplayValue());
    }

    /** @test */
    public function fromCodeReturnsCorrectCurrencyCode()
    {
        $country = Country::fromCode('DK');
        $this->assertEquals('DKK', $country->getCurrencyCode());
    }

    /** @test */
    public function fromCodeReturnsCorrectLanguage()
    {
        $country = Country::fromCode('DK');
        $this->assertEquals('Danish', $country->getLanguage());
    }

    /** @test */
    public function fromCodeReturnsCorrectPhoneCode()
    {
        $country = Country::fromCode('DK');
        $this->assertEquals('+45', $country->getPhoneCode());
    }

    /** @test */
    public function fromCodeReturnsFormatArray()
    {
        $country = Country::fromCode('DK');
        $format = $country->getFormat();
        $this->assertIsArray($format);
        $this->assertArrayHasKey('carbonDate', $format);
        $this->assertArrayHasKey('carbonTime', $format);
        $this->assertArrayHasKey('frontendDate', $format);
    }

    /** @test */
    public function fromCodeReturnsFallbackToOtherForUnknownCode()
    {
        $country = Country::fromCode('XX');
        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals('OT', $country->getCode());
        $this->assertEquals('Other', $country->getDisplayValue());
    }

    /** @test */
    public function valuesReturnsAllTenCountries()
    {
        $values = Country::values();
        $this->assertCount(10, $values);
    }

    /** @test */
    public function valuesContainsExpectedCountryCodes()
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
    public function usCountryHasDifferentDateFormat()
    {
        $us = Country::fromCode('US');
        $format = $us->getFormat();
        $this->assertEquals('mm/dd/yyyy', $format['frontendDate']);
        $this->assertEquals('m/d/Y', $format['carbonDate']);
    }

    /** @test */
    public function fromCodeReturnsGermanyCorrectly()
    {
        $country = Country::fromCode('DE');
        $this->assertEquals('Germany', $country->getDisplayValue());
        $this->assertEquals('EUR', $country->getCurrencyCode());
        $this->assertEquals('German', $country->getLanguage());
    }

    /** @test */
    public function fromCodeReturnsUnitedKingdomCorrectly()
    {
        $country = Country::fromCode('GB');
        $this->assertEquals('United Kingdom', $country->getDisplayValue());
        $this->assertEquals('+44', $country->getPhoneCode());
    }

    /** @test */
    public function otherCountryFallbackIsReturnedForEmptyString()
    {
        $country = Country::fromCode('');
        $this->assertEquals('OT', $country->getCode());
    }

    /** @test */
    public function countryConstructorSetsAllProperties()
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