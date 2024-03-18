<?php

declare(strict_types=1);


namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class SendRateCommand extends Command
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:send-rate')
            // the command description shown when running "php bin/console list"
            ->setDescription('Sends UAH/USD rate to the google analytics')
            // the command help shown when running the command with the "--help" option
            ->setHelp(
                'This is cron command to send UAH/USD rate to the google analytics. It should be run every 5 minutes.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
//        $rates = $this->fetchRates();
//        $output->writeln('Rates fetched successfully');
//        $output->writeln(json_encode($rates));
//
//        $rates = $this->parseRates($rates);
//        $output->writeln('Usd rates:');
//        $output->writeln(json_encode($rates['USD']));

        $rates = $this->fetchNBURates();
//        $output->writeln('Rates fetched successfully');
//        $output->writeln(json_encode($rates));

        $rate = $this->parseNBURatesUSD($rates);
        $output->writeln('Usd rate: ' . $rate);

//        $analytics = $this->configureGA();
//
//        $analytics->setTransactionId(time())
//            ->setEventValue($rate);
//        $analytics->sendPageview();

        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }

    private function parseRates(array $rates): array
    {
        $mappedRates = [];
        foreach ($rates as $rate) {
            $mappedRates[$rate['ccy']] = [
                'buy' => $rate['buy'],
                'sale' => $rate['sale'],
            ];
        }

        return $mappedRates;
    }

    private function fetchRates(): array
    {
        $response = $this->client->request('GET', 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5');
        $content = $response->toArray();

        return $content;
    }

    private function fetchNBURates(): array
    {
        $response = $this->client->request('GET', 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json');
        $content = $response->toArray();

        return $content;
    }

    private function parseNBURatesUSD(array $rates): float
    {
        $usdRate = null;
        foreach ($rates as $rate) {
            if ($rate['cc'] === 'USD') {
                $usdRate = $rate['rate'];
                break;
            }
        }

        return $usdRate;
    }

//    private function configureGA(): Analytics
//    {
//// Instantiate the Analytics object
//// optionally pass TRUE in the constructor if you want to connect using HTTPS
//        $analytics = new Analytics(true);
//
//// Build the GA hit using the Analytics class methods
//// they should Autocomplete if you use a PHP IDE
//        $analytics
//            ->setProtocolVersion('4')
//                ->setTrackingId('G-BDZV4PBLVQ')
//            ->setClientId('12345678')
//            ->setDocumentPath('/main')
//            ->setIpOverride("202.126.106.175");
//
//// When you finish bulding the payload send a hit (such as an pageview or event)
//        return $analytics;
//    }
}
