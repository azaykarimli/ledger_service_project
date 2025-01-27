<?php

namespace App\Tests\Service;

use App\Entity\Balance;
use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Service\BalanceService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class BalanceServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger; // Explicitly declare $logger property
    private BalanceService $balanceService;

    protected function setUp(): void
    {
        // Mock EntityManagerInterface
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Mock LoggerInterface
        $this->logger = $this->createMock(LoggerInterface::class);

        // Instantiate BalanceService with mocked dependencies
        $this->balanceService = new BalanceService($this->entityManager, $this->logger);
    }

    public function testUpdateBalanceWithCreditTransaction(): void
    {
        $balance = new Balance();
        $balance->setBalance(100.00);

        $transaction = new Transaction();
        $transaction->setType(TransactionType::CREDIT);
        $transaction->setAmount(50.00);
        $transaction->setBalance($balance);

        $this->balanceService->updateBalance($transaction);

        $this->assertEquals(150.00, $balance->getBalance());
    }

    public function testUpdateBalanceWithDebitTransaction(): void
    {
        $balance = new Balance();
        $balance->setBalance(100.00);

        $transaction = new Transaction();
        $transaction->setType(TransactionType::DEBIT);
        $transaction->setAmount(30.00);
        $transaction->setBalance($balance);

        $this->balanceService->updateBalance($transaction);

        $this->assertEquals(70.00, $balance->getBalance());
    }
}
