<?php

namespace App\Entity;

use App\Repository\BalanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
#[ORM\Entity(repositoryClass: BalanceRepository::class)]
#[ORM\Table(name: 'balances', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'unique_ledger_currency', columns: ['ledger_id', 'currency'])
])]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class Balance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'balances')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Ledger $ledger = null;

    #[ORM\Column(length: 3)]
    private ?string $currency = null;

    // Ensure DECIMAL type with string for balance
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00'])]
    private ?string $balance = '0.00'; // Keep balance as a string

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\PrePersist]
    public function setUpdatedAtValue(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLedger(): ?Ledger
    {
        return $this->ledger;
    }

    public function setLedger(?Ledger $ledger): static
    {
        $this->ledger = $ledger;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    // Get and set the balance as a string, matching the database column type
    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): static
    {
        // Ensure the balance is a valid string representation of a number
        // You may want to validate or sanitize the value before assigning
        $this->balance = $balance;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
