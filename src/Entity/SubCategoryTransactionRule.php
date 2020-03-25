<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubCategoryTransactionRuleRepository")
 */
class SubCategoryTransactionRule
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contains;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory", inversedBy="subCategoryTransactionRules")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subCategory;

    private $transactionType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContains(): ?string
    {
        return $this->contains;
    }

    public function setContains(string $contains): self
    {
        $this->contains = $contains;

        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): self
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    public function setTransactionType(string $transactionType)
    {
        if (!in_array($transactionType, TransactionType::getAll())) {
            throw new \Exception(sprintf('Invalid transaction type %s', $transactionType));
        }

        $this->transactionType = $transactionType;

        return $this;
    }
}
