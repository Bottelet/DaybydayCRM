<?php

namespace Tests\Unit\Repositories;

use App\Models\Setting;
use App\Repositories\Currency\Currency;
use App\Repositories\Money\Money;
use App\Repositories\Money\MoneyConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Regression guard: Currency's USD entry had thousandSeparator/decimalSeparator
 * swapped (decimalSeparator => ',', thousandSeparator => '.' - the European
 * convention), so every USD amount rendered like "$99,00" instead of "$99.00".
 * Surfaced via a Playwright product-price assertion after a fresh reseed put
 * the app back on its seeded USD default; fixed directly in Currency's
 * hardcoded currency table since nothing else - no other test, no other seed
 * path - relied on the swapped values.
 */
#[Group('repository')]
class CurrencyTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_formats_usd_with_a_period_decimal_separator_and_comma_thousands_separator(): void
    {
        $currency = new Currency('USD');

        $this->assertSame('.', $currency->getDecimalSeparator());
        $this->assertSame(',', $currency->getThousandSeparator());
    }

    #[Test]
    public function it_renders_a_usd_amount_in_the_correct_american_format(): void
    {
        /* Arrange: Money reads Setting::select('currency')->first(), so this
         * test must own its Setting row instead of relying on whatever
         * currency happens to be left over from other tests/seed state. */
        Setting::factory()->create(['currency' => 'USD']);

        /* Act */
        $formatted = (new MoneyConverter(new Money(123456)))->format(false);

        /* Assert */
        $this->assertSame('$1,234.56', $formatted);
    }
}
