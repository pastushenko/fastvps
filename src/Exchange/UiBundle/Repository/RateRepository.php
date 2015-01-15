<?php
namespace Exchange\UiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Exchange\UtilsBundle\Entity\RatesCollection;
use Exchange\UiBundle\Entity\Rate;

class RateRepository extends EntityRepository
{
    const TABLE_NAME = 'rate';
    const CURRENCY_INT_CODE_COL = 'currencyIntCode';

    /** @var RatesCollection */
    private $availableRates;

    public function beginTransaction()
    {
        $this->getEntityManager()->beginTransaction();
    }

    public function rollback()
    {
        $this->getEntityManager()->rollback();
    }

    public function commit()
    {
        $this->getEntityManager()->commit();
    }

    /**
     * @return Rate[]
     */
    public function getDefaultRates()
    {
        $rateIntCodes = $this->getDefaultRateIntCodes();
        return $this->getRates($rateIntCodes);
    }

    /**
     * @param \int[] $rateIntCodes
     * @return Rate[]
     */
    public function getRates($rateIntCodes)
    {
        $this->checkRatesAreAllowed($rateIntCodes);
        $qb = $this->createQueryBuilder(self::TABLE_NAME);
        $qb->where($qb->expr()->in(self::TABLE_NAME.'.'.self::CURRENCY_INT_CODE_COL, $rateIntCodes));
        return $qb->getQuery()->execute();
    }

    /**
     * @return Rate[]
     */
    public function getAllRates()
    {
        $qb = $this->createQueryBuilder(self::TABLE_NAME);
        return $qb->getQuery()->execute();
    }

    /**
     * @param \int[] $rateIntCodes
     * @return array
     */
    public function getRatesAsArray($rateIntCodes)
    {
        $arrayRates = [];
        $rates = $this->getRates($rateIntCodes);
        foreach ($rates as $rate) {
            $arrayRate = array(
                'currencyIntCode' => $rate->getCurrencyIntCode(),
                'currencyCode' => $rate->getCurrencyCode(),
                'currencyName' => $rate->getCurrencyName(),
                'count' => $rate->getCount(),
                'rate' => $rate->getRate(),
                'created' => $rate->getCreated()->format('d-m-Y H:i:s')
            );
            $arrayRates[$rate->getCurrencyIntCode()] = $arrayRate;
        }

        return $arrayRates;
    }

    /**
     * @param RatesCollection $availableRates
     */
    public function setAvailableRates(RatesCollection $availableRates)
    {
        $this->availableRates = $availableRates;
    }

    /**
     * @param Rate $rate
     */
    public function persistRate(Rate $rate)
    {
        $this->getEntityManager()->persist($rate);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->detach($rate);
    }

    /**
     * @return RatesCollection
     * @throws \Exception
     */
    public function getAvailableRatesCollection()
    {
        if (is_null($this->availableRates)) {
            throw new \Exception('You must call method "setAvailableRates" with "RatesCollection" parameter when creating RateRepository.');
        }

        return $this->availableRates;
    }

    /**
     * @return \int[]
     */
    public function getDefaultRateIntCodes()
    {
        return $this->getAvailableRatesCollection()->getDefaultRateIntCodes();
    }

    /**
     * @return \int[]
     */
    private function getAvailableCurrencyIntCodes()
    {
        return $this->getAvailableRatesCollection()->getAvailableRateIntCodes();
    }


    public function dropData()
    {
        $this->createQueryBuilder(self::TABLE_NAME)->delete()->getQuery()->execute();
    }

    /**
     * @param \int[] $rateIntCodes
     * @throws \Exception
     */
    private function checkRatesAreAllowed($rateIntCodes)
    {
        $availableRateIntCodes = $this->getAvailableCurrencyIntCodes();
        $invalidRates = array_diff($rateIntCodes, $availableRateIntCodes);

        if (count($invalidRates)) {
            throw new \Exception(sprintf(
                'You should specify only allowed rate in codes. Rate codes "%s" are not allowed. Allowed rate codes are "%s".',
                implode('", "', $invalidRates),
                implode('", "', $availableRateIntCodes)
            ));
        }
    }
}
