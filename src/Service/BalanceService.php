<?php

namespace App\Service;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\TransactionType;

class BalanceService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateBalance(Transaction $transaction): void
    {
        $balance = $transaction->getBalance();
        $amount = $transaction->getAmount();

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Transaction amount must be positive.');
        }

        if ($transaction->getType()->value === TransactionType::CREDIT->value) {
            $balance->setBalance($balance->getBalance() + $amount);
        } elseif ($transaction->getType()->value === TransactionType::DEBIT->value) {
            if ($balance->getBalance() < $amount) {
                throw new \RuntimeException('Insufficient balance for debit transaction.');
            }
            $balance->setBalance($balance->getBalance() - $amount);
        }

        $this->entityManager->persist($balance);
        $this->entityManager->flush();
    }
}
