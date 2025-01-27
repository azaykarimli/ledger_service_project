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


    public function testCreateTransactionWithNegativeAmount(): void
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

        // Test the POST /api/transactions API with a negative amount
        $this->client->request('POST', '/api/transactions', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'ledger' => '/api/ledgers/' . $ledger->getId(), // Use IRI
            'balance' => '/api/balances/' . $balance->getId(), // Use IRI
            'type' => 'debit',
            'amount' => '-50.00', // Negative amount
            'transaction_id' => uniqid(),
        ]));

        $response = $this->client->getResponse();
        $this->assertEquals(422, $response->getStatusCode()); // Expect a 422 Unprocessable Entity

        // Decode the response and check the error message
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('violations', $responseData); // Ensure the response contains validation errors

        // Check the details of the first violation
        $this->assertArrayHasKey(0, $responseData['violations']);
    }

    public function testCreateLedger(): void
    {
        // Send a POST request to create a ledger
        $this->client->request('POST', '/api/ledgers', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Test Ledger', // Ledger name
            'currency' => 'USD',    // Ledger currency
        ]));

        // Get the response
        $response = $this->client->getResponse();

        // Assert that the response status code is 201 (Created)
        $this->assertEquals(201, $response->getStatusCode());

        // Decode the response content
        $responseData = json_decode($response->getContent(), true);

        // Assert that the response contains the correct data
        $this->assertArrayHasKey('id', $responseData); // Check that the ID is returned
        $this->assertEquals('Test Ledger', $responseData['name']); // Check the name
        $this->assertEquals('USD', $responseData['currency']); // Check the currency
    }

    public function testCreateTransactionWithInvalidJsonPayload(): void
    {
        // Send a malformed JSON payload to the POST /api/transactions endpoint
        $this->client->request('POST', '/api/transactions', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], '{ "ledger": "invalid-json"'); // Malformed JSON

        // Get the response
        $response = $this->client->getResponse();

        // Assert that the response status code is 400 (Bad Request)
        $this->assertEquals(400, $response->getStatusCode());

        // Decode the response content
        $responseData = json_decode($response->getContent(), true);

        // Assert that the response contains the expected error structure
        $this->assertArrayHasKey('detail', $responseData); // Ensure the 'detail' key exists
        $this->assertStringContainsString('Syntax error', $responseData['detail']); // Verify the error message
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
