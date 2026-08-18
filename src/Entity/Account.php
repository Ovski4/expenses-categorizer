<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use App\Validator\Constraints\AccountAliasesAreUniqueConstraint;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[UniqueEntity('name')]
#[AccountAliasesAreUniqueConstraint]
class Account
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected ?string $name = null;

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    #[Assert\Unique(message: 'There are duplicated aliases')]
    protected array $aliases = [];

    #[ORM\Column(type: 'string', length: 3)]
    protected ?string $currency = null;

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<int, string>|null
     */
    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    /**
     * @param array<int, string>|null $aliases
     */
    public function setAliases(?array $aliases): self
    {
        $this->aliases = $aliases;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}
