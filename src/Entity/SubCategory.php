<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Exception;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubCategoryRepository")
 * @ORM\Table(
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="sub_category_unique", columns={"name", "transaction_type"})
 *    }
 * )
 */
class SubCategory
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TopCategory", inversedBy="subCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $top_category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="sub_category")
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubCategoryTransactionRule", mappedBy="sub_category")
     */
    private $subCategoryTransactionRules;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $transaction_type;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

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

    public function getTopCategory(): ?TopCategory
    {
        return $this->top_category;
    }

    public function setTopCategory(?TopCategory $top_category): self
    {
        $this->top_category = $top_category;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setSubCategory($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getSubCategory() === $this) {
                $transaction->setSubCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SubCategoryTransactionRule[]
     */
    public function getSubCategoryTransactionRules(): Collection
    {
        return $this->subCategoryTransactionRules;
    }

    public function addSubCategoryTransactionRule(SubCategoryTransactionRule $subCategoryTransactionRule): self
    {
        if (!$this->subCategoryTransactionRules->contains($subCategoryTransactionRule)) {
            $this->subCategoryTransactionRules[] = $subCategoryTransactionRule;
            $subCategoryTransactionRule->setSubCategory($this);
        }

        return $this;
    }

    public function removeSubCategoryTransactionRule(SubCategoryTransactionRule $subCategoryTransactionRule): self
    {
        if ($this->subCategoryTransactionRules->contains($subCategoryTransactionRule)) {
            $this->subCategoryTransactionRules->removeElement($subCategoryTransactionRule);
            // set the owning side to null (unless already changed)
            if ($subCategoryTransactionRule->getSubCategory() === $this) {
                $subCategoryTransactionRule->setSubCategory(null);
            }
        }

        return $this;
    }

    public function getTransactionType(): ?string
    {
        return $this->transaction_type;
    }

    public function setTransactionType(string $transaction_type)
    {
        if (!in_array($transaction_type, TransactionType::getAll())) {
            throw new Exception(sprintf('Invalid transaction type %s', $transaction_type));
        }

        $this->transaction_type = $transaction_type;

        return $this;
    }
}
