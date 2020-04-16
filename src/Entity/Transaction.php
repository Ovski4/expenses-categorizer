<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Transaction
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
    private $label;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Account")
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory", inversedBy="transactions")
     */
    private $subCategory;

    /**
     * Prevent a wrong subCategory to be set
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function checkSubCategory()
    {
        if ($this->subCategory !== null) {
            if ($this->subCategory->getTransactionType() !== $this->getType()) {
                throw new \Exception(sprintf(
                    'Invalid sub category transaction type (%s) for transaction %s with amount %s',
                    $this->getType(),
                    $this->id,
                    $this->amount
                ));
            }
        }
    }

    public function __toString()
    {
        return sprintf(
            '%s - %s (%s)',
            $this->label,
            $this->created_at->format('Y-m-d H:i:s'),
            $this->amount
        );
    }

    public function toArray()
    {
        $array = [
            'label'        => $this->label,
            'currency'     => $this->account->getCurrency(),
            'account'      => $this->account->getName(),
            'created_at'   => $this->created_at->format('c'),
            'amount'       => $this->amount,
            'type'         => $this->getType()
        ];

        if ($this->getSubCategory() != null) {
            $array['sub_category'] = $this->getSubCategory()->getName();
            $array['top_category'] = $this->getSubCategory()->getTopCategory()->getName();
        }

        return $array;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->amount > 0 ? TransactionType::REVENUES : TransactionType::EXPENSES;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): self
    {
        $this->account = $account;

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
}
