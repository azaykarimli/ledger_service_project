<?php

namespace App\Entity;

use App\Repository\LedgerRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LedgerRepository::class)]
#[ApiResource]
#[ORM\Table(name: '`ledger`')]
#[ORM\HasLifecycleCallbacks]
class Ledger
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 3, nullable: true)]
    #[Assert\Length(min: 3, max: 3)]
    private ?string $currency = null;

    #[ORM\Column(length: 50)]
    private ?string $unique_identifier = null;

    /**
     * @var Collection<int, Balance>
     */
    #[ORM\OneToMany(targetEntity: Balance::class, mappedBy: 'ledger')]
    private Collection $balances;

    public function __construct()
    {
        $this->balances = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setDefaults(): void
    {
        // Set default currency if not provided
        if (empty($this->currency)) {
            $this->currency = 'USD';
        }

        // Generate a unique identifier
        if (empty($this->unique_identifier)) {
            $this->unique_identifier = 'LEDGER-' . strtoupper(uniqid());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getUniqueIdentifier(): ?string
    {
        return $this->unique_identifier;
    }

    public function setUniqueIdentifier(string $unique_identifier): static
    {
        $this->unique_identifier = $unique_identifier;

        return $this;
    }

    /**
     * @return Collection<int, Balance>
     */
    public function getBalances(): Collection
    {
        return $this->balances;
    }

    public function addBalance(Balance $balance): static
    {
        if (!$this->balances->contains($balance)) {
            $this->balances->add($balance);
            $balance->setLedger($this);
        }

        return $this;
    }

    public function removeBalance(Balance $balance): static
    {
        if ($this->balances->removeElement($balance)) {
            // set the owning side to null (unless already changed)
            if ($balance->getLedger() === $this) {
                $balance->setLedger(null);
            }
        }

        return $this;
    }
}
