<?php
namespace Exchange\UtilsBundle\Service;

use Exchange\UiBundle\Entity\Rate;
use Exchange\UtilsBundle\Entity\RatesCollection;

class RatesFetcherService implements RatesFetcherServiceInterface
{
    const RATES_URL_DATE_PATTERN = 'd/m/Y';
    /** @var string */
    private $remoteRatesUrl;

    /**
     * @param $remoteRatesUrl
     */
    public function __construct($remoteRatesUrl)
    {
        $this->remoteRatesUrl = $remoteRatesUrl;
    }

    /**
     * @param \DateTime $dateTime
     * @return RatesCollection
     */
    public function fetchRates(\DateTime $dateTime)
    {
        $url = $this->buildUrl($dateTime);
        $page = $this->getPage($url);

        return $this->extractXmlRates($page);
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    private function buildUrl(\DateTime $dateTime)
    {
        $dateInUrl = $dateTime->format(self::RATES_URL_DATE_PATTERN);
        return sprintf($this->remoteRatesUrl, $dateInUrl);
    }

    /**
     * @param $url
     * @return string
     */
    private function getPage($url)
    {
        return file_get_contents($url);
    }

    /**
     * @param string $page
     * @return RatesCollection
     */
    private function extractXmlRates($page)
    {
        $rates = new RatesCollection();
        $dom = new \DOMDocument();
        $dom->loadXML($page);
        $ratesDom = $dom->getElementsByTagName('Valute');
        /** @var \DOMElement $rateDom */
        foreach ($ratesDom as $rateDom) {
            $currencyIntCode = $rateDom->getElementsByTagName('NumCode')->item(0)->nodeValue;
            $currencyCode = $rateDom->getElementsByTagName('CharCode')->item(0)->nodeValue;
            $count = $rateDom->getElementsByTagName('Nominal')->item(0)->nodeValue;
            $rateExchange = $rateDom->getElementsByTagName('Value')->item(0)->nodeValue;
            $currencyName = $rateDom->getElementsByTagName('Name')->item(0)->nodeValue;

            $rate = new Rate($currencyIntCode);
            $rate->setRate($rateExchange);
            $rate->setCount($count);
            $rate->setCurrencyCode($currencyCode);
            $rate->setCurrencyName($currencyName);

            $rates->addRate($rate);
        }

        return $rates;
    }
}
