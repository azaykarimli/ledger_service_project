<?php


namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\CurlHttpClient;

class LoadTestCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = new CurlHttpClient(); // Use CurlHttpClient for parallel requests
        $endpoint = 'http://localhost:8000/api/transactions';
        $totalRequests = 1000;

        $responses = [];
        for ($i = 0; $i < $totalRequests; $i++) {
            $responses[] = $client->request('POST', $endpoint, [
                'json' => [
                    'ledger_id' => 1,
                    'type' => 'debit',
                    'amount' => 10.00,
                    'currency' => 'USD',
                    'transaction_id' => uniqid(),
                ],
            ]);
        }

        $successfulRequests = 0;
        foreach ($responses as $response) {
            if ($response->getStatusCode() === 201) {
                $successfulRequests++;
            }
        }

        $output->writeln("$successfulRequests/$totalRequests transactions succeeded.");
        return Command::SUCCESS;
    }
}
