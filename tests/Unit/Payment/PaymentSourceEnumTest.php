<?php

namespace Tests\Unit\Payment;

use App\Enums\PaymentSource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\Rules\In;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentSourceEnumTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var string
     */
    private $paymentSource;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentSource = PaymentSource::bank()->getSource();
    }

    #[Test]
    public function getting_source_returns_instance_of_payment_source()
    {
        $this->assertInstanceOf(PaymentSource::class, PaymentSource::fromSource($this->paymentSource));
    }

    #[Test]
    public function payment_source_contains_both_display_and_source_value()
    {
        $this->assertObjectHasAttribute('source', PaymentSource::fromSource($this->paymentSource));
        $this->assertObjectHasAttribute('displayValue', PaymentSource::fromSource($this->paymentSource));
    }

    #[Test]
    public function get_display_value_from_source()
    {
        $this->assertEquals(PaymentSource::fromSource($this->paymentSource)->getDisplayValue(), 'Bank');
    }

    #[Test]
    public function source_returns_correct_source_in_instance()
    {
        $this->assertEquals('cash', PaymentSource::cash()->getSource());
    }

    #[Test]
    public function get_source_from_display_value()
    {
        $this->assertEquals(PaymentSource::fromDisplayValue('Intercompany'), PaymentSource::interCompany()->getSource());
    }

    #[Test]
    public function throws_exception_if_source_is_not_known()
    {
        $this->expectException(\Exception::class);
        PaymentSource::fromSource('None existing source');
    }

    #[Test]
    public function throws_exception_if_display_value_is_not_known()
    {
        $this->expectException(\Exception::class);
        PaymentSource::fromDisplayValue('None existing display value');
    }

    #[Test]
    public function get_validation_rules_for_payment_source()
    {
        $rule = PaymentSource::validationRules();
        $this->assertInstanceOf(In::class, $rule);
        $this->assertObjectHasAttribute('values', $rule);
    }
}
