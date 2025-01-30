<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:load-test-transactions',
    description: 'Simulate 1,000 transactions per minute for load testing.',
)]
class LoadTestTransactionsCommand extends Command
{
    private HttpClientInterface $httpClient;
    private string $apiBaseUrl;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiBaseUrl = $_ENV['API_BASE_URL'] ?? 'http://localhost:8000'; // Default fallback
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $endpoint = "{$this->apiBaseUrl}/api/transactions";
        $ledgerEndpoint = "{$this->apiBaseUrl}/api/ledgers";
        $balanceEndpoint = "{$this->apiBaseUrl}/api/balances";
        $start = microtime(true);
        $totalRequests = 1000;
        $successfulRequests = 0;
        $maxDuration = 60; // Maximum allowed duration in seconds

        $output->writeln("Preparing resources for load testing...");

        // Create a Ledger for the test
        $ledgerResponse = $this->httpClient->request('POST', $ledgerEndpoint, [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'name' => 'Test Ledger',
                'currency' => 'USD',
            ],
        ]);

        if ($ledgerResponse->getStatusCode() !== 201) {
            $output->writeln('<error>Failed to create ledger for load testing.</error>');
            return Command::FAILURE;
        }

        $ledgerData = json_decode($ledgerResponse->getContent(), true);
        if (!isset($ledgerData['@id'])) {
            $output->writeln('<error>Failed to retrieve ledger IRI.</error>');
            return Command::FAILURE;
        }

        // Create a Balance for the test
        $balanceResponse = $this->httpClient->request('POST', $balanceEndpoint, [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'ledger' => $ledgerData['@id'], // Use IRI
                'currency' => 'USD',
                'balance' => (string) 10000.00, // Convert to string
            ],
        ]);

        if ($balanceResponse->getStatusCode() !== 201) {
            $output->writeln('<error>Failed to create balance for load testing.</error>');
            return Command::FAILURE;
        }

        $balanceData = json_decode($balanceResponse->getContent(), true);
        if (!isset($balanceData['@id'])) {
            $output->writeln('<error>Failed to retrieve balance IRI.</error>');
            return Command::FAILURE;
        }

        $output->writeln("Starting load test for $totalRequests transactions...");

        // Send requests as fast as possible
        for ($i = 1; $i <= $totalRequests; $i++) {
            $elapsedTime = microtime(true) - $start;

            // Stop if the time limit is exceeded
            if ($elapsedTime > $maxDuration) {
                $output->writeln("<error>Time limit exceeded. Load test took longer than {$maxDuration} seconds.</error>");
                return Command::FAILURE;
            }

            try {
                $response = $this->httpClient->request('POST', $endpoint, [
                    'headers' => [
                        'Content-Type' => 'application/ld+json',
                    ],
                    'json' => [
                        'ledger' => $ledgerData['@id'], // Use IRI
                        'balance' => $balanceData['@id'], // Use IRI
                        'type' => 'debit',
                        'amount' => (string) 10.00,
                        'transaction_id' => uniqid(),
                    ],
                ]);

                if ($response->getStatusCode() === 201) {
                    $successfulRequests++;
                } else {
                    $output->writeln("<error>Transaction $i failed with status code: {$response->getStatusCode()}</error>");
                }
            } catch (TransportExceptionInterface $e) {
                $output->writeln("<error>Transaction $i failed: {$e->getMessage()}</error>");
            }
        }

        $duration = microtime(true) - $start;

        $output->writeln("Load test completed in " . round($duration, 2) . " seconds.");
        $output->writeln("$successfulRequests/$totalRequests transactions succeeded.");

        // Cleanup: Delete the test ledger and balance
        $output->writeln("Cleaning up test resources...");

        try {
            // Delete the balance
            $this->httpClient->request('DELETE', "{$this->apiBaseUrl}{$balanceData['@id']}");

            // Delete the ledger
            $this->httpClient->request('DELETE', "{$this->apiBaseUrl}{$ledgerData['@id']}");
        } catch (TransportExceptionInterface $e) {
            $output->writeln("<error>Failed to clean up resources: {$e->getMessage()}</error>");
        }

        return Command::SUCCESS;
    }
}
