<?php

declare(strict_types=1);


namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SendRateCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $gaMeasurementId,
        private readonly string $gaApiSecret,
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
        $rates = $this->fetchNBURates();
        $rate = $this->parseNBURatesUSD($rates);
        $output->writeln('Usd rate: ' . $rate);

        $this->sendMeasurement($rate);

        return Command::SUCCESS;
    }

    private function fetchNBURates(): array
    {
        $response = $this->client->request('GET', 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json');

        return $response->toArray();
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

    private function sendMeasurement(float $rate): void
    {
        // Your Measurement ID from GA4
        $measurement_id = $this->gaMeasurementId;

// Your API Secret created in the GA4 interface
        $api_secret = $this->gaApiSecret;

// Client ID to associate data with a unique user
        $client_id = '12345'; // You can generate this or use an existing value

// Prepare the payload
        $data = [
            'client_id' => $client_id,
            'events' => [
                [
                    'name' => 'rate_update', // Customize your event name
                    'params' => [
                        'rate' => $rate, // Your custom metric value
                    ],
                ],
            ],
        ];

// Encode data to JSON
        $json_data = json_encode($data);

// Setup the API endpoint
        $url = "https://www.google-analytics.com/mp/collect?measurement_id=$measurement_id&api_secret=$api_secret";

// Initialize cURL
        $ch = curl_init();

// Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the cURL session
        curl_exec($ch);

// Close cURL session
        curl_close($ch);
    }
}
