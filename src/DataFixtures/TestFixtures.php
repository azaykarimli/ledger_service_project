<?php

namespace App\DataFixtures;

use App\Entity\Ledger;
use App\Entity\Balance;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create a test Ledger
        $ledger = new Ledger();
        $ledger->setName('Test Ledger');
        $ledger->setCurrency('USD');
        $manager->persist($ledger);

        // Create a test Balance
        $balance = new Balance();
        $balance->setLedger($ledger);
        $balance->setCurrency('USD');
        $balance->setBalance(100.00);
        $balance->setUpdatedAt(new \DateTime());
        $manager->persist($balance);

        $manager->flush();
    }
}
