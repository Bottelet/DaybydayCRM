<?php

namespace Tests\Unit\Payment;

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

    // region happy_path

    #[Test]
    #[Group('junie_repaired')]
    public function getting_source_returns_instance_of_payment_source()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $result = PaymentSource::fromSource($this->paymentSource);

        /** Assert */
        $this->assertInstanceOf(PaymentSource::class, $result);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function payment_source_contains_both_display_and_source_value()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $paymentSource = PaymentSource::fromSource($this->paymentSource);

        /** Assert */
        $this->assertTrue(property_exists($paymentSource, 'source'));
        $this->assertTrue(property_exists($paymentSource, 'displayValue'));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function get_display_value_from_source()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $displayValue = PaymentSource::fromSource($this->paymentSource)->getDisplayValue();

        /** Assert */
        $this->assertEquals('Bank', $displayValue);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function source_returns_correct_source_in_instance()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $source = PaymentSource::cash()->getSource();

        /** Assert */
        $this->assertEquals('cash', $source);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function get_source_from_display_value()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $source = PaymentSource::fromDisplayValue('Intercompany');

        /** Assert */
        $this->assertEquals(PaymentSource::interCompany()->getSource(), $source);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function get_validation_rules_for_payment_source()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $rule = PaymentSource::validationRules();

        /** Assert */
        $this->assertInstanceOf(In::class, $rule);
        $this->assertTrue(property_exists($rule, 'values'));
    }

    // endregion

    // region failure_path

    #[Test]
    #[Group('junie_repaired')]
    public function throws_exception_if_source_is_not_known()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act & Assert */
        $this->expectException(Exception::class);
        PaymentSource::fromSource('None existing source');
    }

    #[Test]
    #[Group('junie_repaired')]
    public function throws_exception_if_display_value_is_not_known()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act & Assert */
        $this->expectException(Exception::class);
        PaymentSource::fromDisplayValue('None existing display value');
    }

    // endregion
}
