<?php
namespace Exchange\UiBundle\Controller;

use Exchange\UiBundle\Repository\RateRepository;
use Exchange\UtilsBundle\Service\RatesUpdaterService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
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
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('ExchangeUiBundle:Home:index.html.twig', array(
            'rates' => $this->rateRepository->getDefaultRates(),
            'availableRates' => $this->rateRepository->getAvailableRatesCollection()->getRates(),
            'defaultRateIntCodes' => $this->rateRepository->getDefaultRateIntCodes()
        ));
    }
}
