<?php
namespace Exchange\UiBundle\Entity;

use Exchange\UtilsBundle\Helper\UtcDateTime;

class Rate
{
    /** @var int */
    private $currencyIntCode;
    /** @var string */
    private $currencyCode;
    /** @var string */
    private $currencyName;
    /** @var int */
    private $count = 1;
    /** @var float */
    private $rate;
    /** @var \DateTime */
    private $created;

    /**
     * @param int $currencyIntCode
     */
    public function __construct($currencyIntCode)
    {
        $this->currencyIntCode = (int) $currencyIntCode;
        $this->created = UtcDateTime::getDateTime();
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return int
     */
    public function getCurrencyIntCode()
    {
        return $this->currencyIntCode;
    }

    /**
     * @return int
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return string
     */
    public function getCurrencyName()
    {
        return $this->currencyName;
    }

    /**
     * @param string $currencyName
     */
    public function setCurrencyName($currencyName)
    {
        $this->currencyName = $currencyName;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     */
    public function setRate($rate)
    {
        $this->rate = (float) $rate;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
}
