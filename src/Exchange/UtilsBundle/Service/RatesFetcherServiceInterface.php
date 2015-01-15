<?php
namespace Exchange\UtilsBundle\Service;

use Exchange\UtilsBundle\Entity\RatesCollection;

interface RatesFetcherServiceInterface
{
    /**
     * @param \DateTime $dateTime
     * @return RatesCollection
     */
    public function fetchRates(\DateTime $dateTime);
}
