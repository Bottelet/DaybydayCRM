<?php
namespace  App\Repositories\Money;

class MoneyConverter
{
    private $money;

    public function __construct(Money $money)
    {
        $this->money = $money;
    }

    /**
     * Format amount to currency equivalent string.
     * @param bool $useCode
     * @return string
     */
    public function format($useCode = true)
    {
        if ($this->money->getCurrency()->getCode() && $useCode == true) {
            return $this->codeFormat();
        } elseif ($this->money->getCurrency()->getSymbolPlacement() == 'before') {
            return $this->symbolBeforeFormat();
        } else {
            return $this->symbolAfterFormat();
        }
    }
    public function codeFormat()
    {
        return $this->currencyFormat() . ' ' . $this->money->getCurrency()->getCode();
    }
    public function symbolBeforeFormat()
    {
        return $this->money->getCurrency()->getSymbol().$this->currencyFormat();
    }
    public function symbolAfterFormat()
    {
        return $this->currencyFormat() . ' ' . $this->money->getCurrency()->getSymbol();
    }
    /**
     * Get amount formatted to currency.
     *
     * @return mixed
     */
    public function currencyFormat()
    {
        // Return western format
        return number_format(
            $this->money->getBigDecimalAmount(),
            $this->money->getCurrency()->getPrecision(),
            $this->money->getCurrency()->getDecimalSeparator(),
            $this->money->getCurrency()->getThousandSeparator()
        );
    }
}
