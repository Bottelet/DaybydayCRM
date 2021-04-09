<?php
namespace Tests\Unit\Payment;

use App\Enums\PaymentSource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Tests\TestCase;

class PaymentSourceEnumTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var string
     */
    private $paymentSource;

    public function setUp(): void
    {
        parent::setUp();
        $this->paymentSource = PaymentSource::bank()->getSource();
    }

    /** @test */
    public function gettingSourceReturnsInstanceOfPaymentSource()
    {
        $this->assertInstanceOf(PaymentSource::class, PaymentSource::fromSource($this->paymentSource));
    }

    /** @test */
    public function PaymentSourceContainsBothDisplayAndSourceValue()
    {
        $this->assertObjectHasAttribute("source", PaymentSource::fromSource($this->paymentSource));
        $this->assertObjectHasAttribute("displayValue", PaymentSource::fromSource($this->paymentSource));
    }

    /** @test */
    public function getDisplayValueFromSource()
    {
        $this->assertEquals(PaymentSource::fromSource($this->paymentSource)->getDisplayValue(), "Bank");
    }

    /** @test */
    public function sourceReturnsCorrectSourceInInstance()
    {
        $this->assertEquals("cash", PaymentSource::cash()->getSource());
    }

    /** @test */
    public function getSourceFromDisplayValue()
    {
        $this->assertEquals(PaymentSource::fromDisplayValue("Intercompany"), PaymentSource::interCompany()->getSource());
    }

    /** @test */
    public function throwsExceptionIfSourceIsNotKnown()
    {
        $this->expectException(\Exception::class);
        PaymentSource::fromSource("None existing source");
    }

    /** @test */
    public function throwsExceptionIfDisplayValueIsNotKnown()
    {
        $this->expectException(\Exception::class);
        PaymentSource::fromDisplayValue("None existing display value");
    }

    /** @test */
    public function getValidationRulesForPaymentSource()
    {
        $rule = PaymentSource::validationRules();
        $this->assertInstanceOf(In::class, $rule);
        $this->assertObjectHasAttribute("values", $rule);
    }
}
