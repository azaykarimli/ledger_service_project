<?php

namespace App\DataFixtures;

use App\Entity\Transaction;
use App\Entity\Ledger;
use App\Entity\Balance;
use App\Enum\TransactionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TransactionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create a Ledger
        $ledger = new Ledger();
        $ledger->setName('Test Ledger');
        $ledger->setCurrency('USD');
        $manager->persist($ledger);

        // Create a Balance
        $balance = new Balance();
        $balance->setLedger($ledger);
        $balance->setCurrency('USD');
        $balance->setBalance(1000.00); // Initial balance
        $balance->setUpdatedAt(new \DateTime());
        $manager->persist($balance);

        // Create Transactions
        for ($i = 1; $i <= 10; $i++) {
            $transaction = new Transaction();
            $transaction->setLedger($ledger);
            $transaction->setBalance($balance);
            $transaction->setType($i % 2 === 0 ? TransactionType::DEBIT : TransactionType::CREDIT);
            $transaction->setAmount($i * 10.00); // Example amounts: 10.00, 20.00, etc.
            $transaction->setTransactionId('TX-' . uniqid());
            $transaction->setCreatedAt(new \DateTime());

            $manager->persist($transaction);
        }

        // Save everything to the database
        $manager->flush();
    }
}
