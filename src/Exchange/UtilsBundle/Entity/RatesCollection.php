<?php
namespace Exchange\UtilsBundle\Entity;

use Exchange\UiBundle\Entity\Rate;
use Exchange\UtilsBundle\Exception\RateNotFoundException;

class RatesCollection
{
    /** @var Rate[] */
    private $rates = [];
    /** @var \int[] */
    private $defaultRateIntCodes = [];
    /** @var \int[] */
    private $currencyIntCodes = [];

    /**
     * @param int $currencyIntCode
     * @param string $currencyCode
     * @param bool $isDefaultRate
     */
    public function addRateCode($currencyIntCode, $currencyCode, $isDefaultRate = false)
    {
        $rate = new Rate($currencyIntCode);
        $rate->setCurrencyCode($currencyCode);
        $this->rates[$rate->getCurrencyIntCode()] = $rate;
        $this->currencyIntCodes[$rate->getCurrencyIntCode()] = $currencyIntCode;

        if ($isDefaultRate) {
            $this->defaultRateIntCodes[$rate->getCurrencyIntCode()] = $rate->getCurrencyIntCode();
        }
    }

    /**
     * @param Rate $rate
     */
    public function addRate(Rate $rate)
    {
        $this->rates[$rate->getCurrencyIntCode()] = $rate;
        $this->currencyIntCodes[$rate->getCurrencyIntCode()] = $rate->getCurrencyIntCode();
    }

    /**
     * @return Rate[]
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * @return Rate[]
     */
    public function getDefaultRateIntCodes()
    {
        return $this->defaultRateIntCodes;
    }

    /**
     * @return \int[]
     */
    public function getAvailableRateIntCodes()
    {
        return $this->currencyIntCodes;
    }

    /**
     * @param int $currencyIntCode
     * @return Rate
     * @throws RateNotFoundException
     */
    public function getRate($currencyIntCode)
    {
        if (!isset($this->rates[$currencyIntCode])) {
            throw new RateNotFoundException(sprintf('Rate with currency int code "%s" not exists.', $currencyIntCode));
        }

        return $this->rates[$currencyIntCode];
    }
}
