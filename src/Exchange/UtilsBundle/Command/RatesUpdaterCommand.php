<?php
namespace Exchange\UtilsBundle\Command;

use Exchange\UtilsBundle\Exception\NoNeedToUpdateRatesException;
use Exchange\UtilsBundle\Helper\UtcDateTime;
use Exchange\UtilsBundle\Service\RatesUpdaterService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RatesUpdaterCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rates:update')
            ->setDescription('Rates updater service')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $dateTime = UtcDateTime::getDateTime();
        $output->writeln(sprintf('<info>Updating rates for "%s".</info>', $dateTime->format('d-m-Y')));

        /** @var RatesUpdaterService $ratesUpdaterService */
        $ratesUpdaterService = $this->getContainer()->get('rates_updater_service_for_cron');
        try {
            $ratesUpdaterService->updateRatesIfNeed($dateTime);
            $output->writeln(sprintf('<info>Rates successful updated.</info>'));
        } catch (NoNeedToUpdateRatesException $ex) {
            $output->writeln(sprintf('<info>Rates already updated. Rates can be updated from console command once in %s seconds.</info>', $ratesUpdaterService->getDataLifetime()));
        }

        return 0;
    }

}