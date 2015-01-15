<?php
namespace Exchange\UiBundle\Controller;

use Exchange\UiBundle\Repository\RateRepository;
use Exchange\UtilsBundle\Exception\NoNeedToUpdateRatesException;
use Exchange\UtilsBundle\Helper\UtcDateTime;
use Exchange\UtilsBundle\Service\RatesUpdaterService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RatesApiController extends Controller
{
    const SHOWN_RATES_KEY = 'showRates';

    const RATES_SUCCESSFUL_UPDATED_STATUS = 1;
    const RATES_SUCCESSFUL_UPDATED_MESSAGE = 'OK';

    const RATES_ALREADY_UP_TO_DATE_STATUS = 2;
    const RATES_ALREADY_UP_TO_DATE_MESSAGE = 'Rates already updated. You can update rates once in %s seconds.';

    const RATES_NOT_UPDATED_STATUS = 0;
    const RATES_NOT_UPDATED_MESSAGE = 'ERROR: %s';

    const RATES_SUCCESSFUL_REQUESTED_STATUS = '1';
    const RATES_SUCCESSFUL_REQUESTED_MESSAGE = 'OK';

    const RATES_NOT_REQUESTED_STATUS = '0';
    const RATES_NOT_REQUESTED_MESSAGE = 'ERROR: %s';

    const AVAILABLE_DELIMITER_COMMA = ',';
    const AVAILABLE_DELIMITER_EOL = PHP_EOL;
    const AVAILABLE_DELIMITER_SEMICOLON = ';';

    /** @var array */
    private $availableDelimiters = [
        self::AVAILABLE_DELIMITER_COMMA,
        self::AVAILABLE_DELIMITER_EOL,
        self::AVAILABLE_DELIMITER_SEMICOLON,
    ];
    /** @var RateRepository */
    private $rateRepository;
    /** @var RatesUpdaterService */
    private $ratesUpdaterService;

    /**
     * @param RateRepository $rateRepository
     * @param RatesUpdaterService $ratesUpdaterService
     */
    public function __construct(RateRepository $rateRepository, RatesUpdaterService $ratesUpdaterService)
    {
        $this->rateRepository = $rateRepository;
        $this->ratesUpdaterService = $ratesUpdaterService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getAction(Request $request)
    {
        $ratesData = [];

        try {
            $requestedRates = $this->getRequestedRates($request);
            $ratesData = $this->rateRepository->getRatesAsArray($requestedRates);

            $status = self::RATES_SUCCESSFUL_REQUESTED_STATUS;
            $message = self::RATES_SUCCESSFUL_REQUESTED_MESSAGE;
        } catch (\Exception $ex) {
            $status = self::RATES_NOT_REQUESTED_STATUS;
            $message = sprintf(self::RATES_NOT_REQUESTED_MESSAGE, $ex->getMessage());
        }

        return $this->render('ExchangeUiBundle:Home:json.html.twig', array(
            'response' => array(
                'status' => $status,
                'message' => $message,
                'rates' => $ratesData
            )
        ));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function updateAction(Request $request)
    {
        $currentDateTime = UtcDateTime::getDateTime();
        $ratesData = [];

        try {
            $this->ratesUpdaterService->updateRatesIfNeed($currentDateTime);
            $requestedRates = $this->getRequestedRates($request);
            $ratesData = $this->rateRepository->getRatesAsArray($requestedRates);

            $status = self::RATES_SUCCESSFUL_UPDATED_STATUS;
            $message = self::RATES_SUCCESSFUL_UPDATED_MESSAGE;

        } catch (NoNeedToUpdateRatesException $ex) {
            $status = self::RATES_ALREADY_UP_TO_DATE_STATUS;
            $message = sprintf(self::RATES_ALREADY_UP_TO_DATE_MESSAGE, $this->ratesUpdaterService->getDataLifetime());

        } catch (\Exception $ex) {
            $status = self::RATES_NOT_UPDATED_STATUS;
            $message = sprintf(self::RATES_NOT_UPDATED_MESSAGE, $ex->getMessage());

        }

        return $this->render('ExchangeUiBundle:Home:json.html.twig', array(
            'response' => array(
                'status' => $status,
                'message' => $message,
                'rates' => $ratesData
            )
        ));
    }

    /**
     * @param Request $request
     * @return \int[]
     * @throws \Exception
     */
    private function getRequestedRates(Request $request)
    {
        $requestedRatesString = $request->get(self::SHOWN_RATES_KEY);

        if (is_null($requestedRatesString)) {
            throw new \Exception(sprintf('Url param "%s" is missing. Please specify requested rate int codes.', self::SHOWN_RATES_KEY));
        }

        $requestedRatesString = str_replace($this->availableDelimiters, PHP_EOL, $requestedRatesString);
        $requestedRates = explode(PHP_EOL, $requestedRatesString);

        $cleanRequestedRates = [];
        foreach($requestedRates as $rate) {
            $rate = trim($rate);
            if (!$rate) {
                continue;
            }

            if (!ctype_digit($rate)) {
                throw new \Exception(sprintf('Url param "%s" must contain only rate int codes and delimiters. Wrong rate code: "%s".', self::SHOWN_RATES_KEY, $rate));
            }

            $cleanRequestedRates[$rate] = $rate;
        }

        return $cleanRequestedRates;
    }
}
