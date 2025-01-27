<?php

namespace App\Tests\Controller;

use App\Entity\Ledger;
use App\Entity\Balance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransactionControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp(); // Call parent::setUp()

        $this->client = static::createClient();
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        // Ensure the database is empty before each test
        $this->clearDatabase();
    }

    protected function tearDown(): void
    {
        // Ensure the database is empty after each test
        $this->clearDatabase();

        parent::tearDown(); // Call parent::tearDown()
    }

    private function clearDatabase(): void
    {
        $tables = ['transactions', 'balances', 'ledger'];
        foreach ($tables as $table) {
            $this->entityManager->getConnection()->executeQuery("TRUNCATE TABLE $table RESTART IDENTITY CASCADE");
        }
    }

    public function testCreateTransaction(): void
    {
        // Set up a Ledger and Balance in the test database
        $ledger = new Ledger();
        $ledger->setName('Test Ledger');
        $ledger->setCurrency('USD');
        $this->entityManager->persist($ledger);

        $balance = new Balance();
        $balance->setLedger($ledger);
        $balance->setCurrency('USD');
        $balance->setBalance('100.00');
        $this->entityManager->persist($balance);

        $this->entityManager->flush();

        // Test the POST /api/transactions API
        $this->client->request('POST', '/api/transactions', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'ledger' => '/api/ledgers/' . $ledger->getId(), // Use IRI
            'balance' => '/api/balances/' . $balance->getId(), // Use IRI
            'type' => 'debit',
            'amount' => '50.00',
            'transaction_id' => uniqid(),
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
    }


    public function testGetBalances(): void
    {
        // Set up a Ledger and Balance in the test database
        $ledger = new Ledger();
        $ledger->setName('Test Ledger');
        $ledger->setCurrency('USD');
        $this->entityManager->persist($ledger);

        $balance = new Balance();
        $balance->setLedger($ledger);
        $balance->setCurrency('USD');
        $balance->setBalance('100.00');
        $this->entityManager->persist($balance);

        $this->entityManager->flush();

        // Test API endpoint
        $this->client->request('GET', '/api/balances/' . $ledger->getId());

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        // Assert the response contains the correct balance data
        $this->assertEquals(1, $responseData['id']); // Check the balance ID
        $this->assertEquals('USD', $responseData['currency']); // Check the currency
        $this->assertEquals('100.00', $responseData['balance']); // Check the balance
        $this->assertEquals('/api/ledgers/' . $ledger->getId(), $responseData['ledger']); // Check the ledger reference
    }
}
