<?php
namespace Exchange\UtilsBundle\Service;

use Exchange\UiBundle\Entity\Rate;
use Exchange\UiBundle\Repository\RateRepository;
use Exchange\UtilsBundle\Entity\RatesCollection;
use Exchange\UtilsBundle\Exception\NoNeedToUpdateRatesException;
use Exchange\UtilsBundle\Exception\RateNotFoundException;
use Exchange\UtilsBundle\Helper\UtcDateTime;

class RatesUpdaterService
{
    const RATES_URL_DATE_PATTERN = 'd/m/Y';

    /** @var RateRepository */
    private $rateRepository;
    /** @var RatesFetcherServiceInterface */
    private $ratesFetcherService;
    /** @var int */
    private $dataLifeTime;

    /**
     * @param RateRepository $rateRepository
     * @param RatesFetcherServiceInterface $ratesFetcherService
     * @param $dataLifeTime
     */
    public function __construct(RateRepository $rateRepository, RatesFetcherServiceInterface $ratesFetcherService, $dataLifeTime)
    {
        $this->rateRepository = $rateRepository;
        $this->ratesFetcherService = $ratesFetcherService;
        $this->dataLifeTime = $dataLifeTime;
    }

    /**
     * @param \DateTime $dateTime
     * @throws NoNeedToUpdateRatesException
     * @throws \Exception
     */
    public function updateRatesIfNeed(\DateTime $dateTime)
    {
        if (!$this->isNeedToUpdateRates()) {
            throw new NoNeedToUpdateRatesException();
        }

        $this->updateRates($dateTime);
    }

    /**
     * @return int
     */
    public function getDataLifetime()
    {
        return $this->dataLifeTime;
    }

    /**
     * @param \DateTime $dateTime
     * @throws \Exception
     */
    private function updateRates(\DateTime $dateTime)
    {
        $remoteRates = $this->ratesFetcherService->fetchRates($dateTime);
        $availableRates = $this->rateRepository->getAvailableRatesCollection()->getRates();

        $this->rateRepository->beginTransaction();
        $this->rateRepository->dropData();
        try {
            foreach($availableRates as $availableRate) {
                $rate = $remoteRates->getRate($availableRate->getCurrencyIntCode());
                $this->rateRepository->persistRate($rate);
            }
        } catch (RateNotFoundException $ex) {
            $this->rateRepository->rollback();
            throw new \Exception($ex->getMessage().' Rate not found in remote xml rates.');
        }
        $this->rateRepository->commit();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isNeedToUpdateRates()
    {
        $availableRates = $this->rateRepository->getAvailableRatesCollection();
        if (!$availableRates) {
            throw new \Exception('There are no available rates in config file.');
        }

        $rates = $this->rateRepository->getAllRates();
        if (!$rates) {
            return true;
        }

        if ($this->isThereNewRates($availableRates->getRates(), $rates)) {
            return true;
        }

        foreach($rates as $rate) {
            if ($this->isNeedToUpdateRate($rate, $this->dataLifeTime)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Rate $rate
     * @param int $dataLifeTime
     * @return bool
     */
    private function isNeedToUpdateRate(Rate $rate, $dataLifeTime)
    {
        $date = date('m/d/Y H:i:s', $rate->getCreated()->getTimestamp());
        $validTill = UtcDateTime::getDateTime($date)->getTimestamp() + $dataLifeTime;
        $currentTime = UtcDateTime::getDateTime()->getTimestamp();

        return $currentTime > $validTill;
    }

    /**
     * @param Rate[] $availableRates
     * @param Rate[] $rates
     * @return bool
     */
    private function isThereNewRates($availableRates, $rates)
    {
        foreach($availableRates as $availableRate) {
            $rateExist = false;
            foreach($rates as $rate) {
                if ($availableRate->getCurrencyIntCode() == $rate->getCurrencyIntCode()) {
                    $rateExist = true;
                    break;
                }
            }

            if (!$rateExist) {
                return true;
            }
        }

        return false;
    }
}
