<?php

namespace Tests\Unit\Payments;

use App\Enums\PaymentSource;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\In;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class PaymentSourceEnumTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var string */
    private $paymentSource;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentSource = PaymentSource::bank()->getSource();
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_returns_instance_of_payment_source_when_getting_source()
    {
        /* Arrange */

        /* Act */
        $result = PaymentSource::fromSource($this->paymentSource);

        /* Assert */
        $this->assertInstanceOf(PaymentSource::class, $result);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_verifies_payment_source_contains_both_display_and_source_value()
    {
        /* Arrange */

        /* Act */
        $paymentSource = PaymentSource::fromSource($this->paymentSource);

        /* Assert */
        $this->assertTrue(property_exists($paymentSource, 'source'));
        $this->assertTrue(property_exists($paymentSource, 'displayValue'));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_gets_display_value_from_source()
    {
        /* Arrange */

        /* Act */
        $displayValue = PaymentSource::fromSource($this->paymentSource)->getDisplayValue();

        /* Assert */
        $this->assertEquals('Bank', $displayValue);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_returns_correct_source_in_instance()
    {
        /* Arrange */

        /* Act */
        $source = PaymentSource::cash()->getSource();

        /* Assert */
        $this->assertEquals('cash', $source);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_gets_source_from_display_value()
    {
        /* Arrange */

        /* Act */
        $source = PaymentSource::fromDisplayValue('Intercompany');

        /* Assert */
        $this->assertEquals(PaymentSource::interCompany()->getSource(), $source);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_gets_validation_rules_for_payment_source()
    {
        /* Arrange */

        /* Act */
        $rule = PaymentSource::validationRules();

        /* Assert */
        $this->assertInstanceOf(In::class, $rule);
        $this->assertTrue(property_exists($rule, 'values'));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_throws_exception_if_source_is_not_known()
    {
        /* Arrange */

        /* Act & Assert */
        $this->expectException(Exception::class);
        PaymentSource::fromSource('None existing source');
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_throws_exception_if_display_value_is_not_known()
    {
        /* Arrange */

        /* Act & Assert */
        $this->expectException(Exception::class);
        PaymentSource::fromDisplayValue('None existing display value');
    }
}
