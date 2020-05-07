<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints\RuleIsCompleteConstraint;
use App\Validator\Constraints\RuleIsLogicalConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubCategoryTransactionRuleRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(
 *            name="sub_category_transaction_rule_unique",
 *            columns={"contains", "sub_category_id"})
 *    }
 * )
 * @RuleIsCompleteConstraint
 * @RuleIsLogicalConstraint
 * @UniqueEntity(
 *     fields={"contains", "subCategory"},
 *     errorPath="contains",
 *     message="rule.already_exists"
 * )
 */
class SubCategoryTransactionRule
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
    private $contains;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $operator;

    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     * @Assert\Positive
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory", inversedBy="subCategoryTransactionRules")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $subCategory;

    /**
     * @ORM\Column(type="smallint", options={"default": "0"})
     */
    private $priority;

    /**
     * This field is not needed in the database as we compute the sign of the amount
     */
    private $transactionType;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $updatedAt;

    public function __construct()
    {
        $this->priority = 0;
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAtToNow()
    {
        $this->setUpdatedAt(new \DateTime('now'));
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function checkOperatorAndAmountFields()
    {
        if (
            ($this->amount !== null && $this->operator == null) ||
            ($this->amount == null && $this->operator != null)
        ) {
            throw new \Exception(
                'Entity SubCategoryTransactionRule must have both operator and amount set or none'
            );
        }
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function checkSubCategory()
    {
        if ($this->subCategory->getTransactionType() !== $this->transactionType) {
            throw new \Exception(sprintf(
                'Invalid sub category transaction type (%s) for transaction %s with amount %s',
                $this->subCategory->getTransactionType(),
                $this->id,
                $this->amount
            ));
        }
    }

    /**
     * From a usable format to a database format
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setAmountAndOperatorBeforePersist()
    {
        if ($this->transactionType == TransactionType::EXPENSES) {

            if ($this->operator == Operator::GREATER_THAN_OR_EQUAL) {
                $this->operator = Operator::LOWER_THAN_OR_EQUAL;
            } else if ($this->operator == Operator::LOWER_THAN_OR_EQUAL) {
                $this->operator = Operator::GREATER_THAN_OR_EQUAL;
            }

            $this->amount = $this->amount !== null ? -abs($this->amount) : null;
        }
    }

    /**
     * From database format to a more usable format
     *
     * @ORM\PostLoad
     */
    public function setAmountAndOperatorAfterLoad()
    {
        $this->transactionType = $this->subCategory->getTransactionType();

        if ($this->transactionType == TransactionType::EXPENSES) {

            if ($this->operator == Operator::GREATER_THAN_OR_EQUAL) {
                $this->operator = Operator::LOWER_THAN_OR_EQUAL;
            } else if ($this->operator == Operator::LOWER_THAN_OR_EQUAL) {
                $this->operator = Operator::GREATER_THAN_OR_EQUAL;
            }

            $this->amount = $this->amount !== null ? abs($this->amount) : null;
        }

        return $this;
    }

    public function toArray()
    {
        $array = [
            'id'           => $this->id,
            'contains'     => $this->contains,
            'operator'     => $this->operator,
            'amount'       => $this->amount,
            'sub_category' => $this->subCategory->getName(),
            'type'         => $this->subCategory->getTransactionType(),
            'priority'     => $this->getPriority()
        ];

        return $array;
    }

    public function getId(): ?string
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

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

    public function setTransactionType(?string $transactionType)
    {
        if ($transactionType !== null && !in_array($transactionType, TransactionType::getAll())) {
            throw new \Exception(sprintf('Invalid transaction type %s', $transactionType));
        }

        $this->transactionType = $transactionType;

        return $this;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator)
    {
        if ($operator !== null && !in_array($operator, Operator::getAll())) {
            throw new \Exception(sprintf('Invalid operator %s', $operator));
        }

        $this->operator = $operator;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
