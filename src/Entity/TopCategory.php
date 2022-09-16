<?php

namespace App\Entity;

use App\Repository\TopCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TopCategoryRepository::class)]
#[ORM\UniqueConstraint(name: 'top_category_unique', columns: ['name', 'transaction_type'])]
#[UniqueEntity(fields: ['name', 'transactionType'])]
class TopCategory
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected $id;

    #[ORM\Column(type: 'string', length: 255)]
    protected  ?string $name = null;

    #[ORM\OneToMany(targetEntity: SubCategory::class, mappedBy: 'topCategory')]
    protected $subCategories;

    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $transactionType = null;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
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

    /**
     * @return Collection|SubCategory[]
     */
    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    public function addSubCategory(SubCategory $subCategory): self
    {
        if (!$this->subCategories->contains($subCategory)) {
            $this->subCategories[] = $subCategory;
            $subCategory->setTopCategory($this);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory): self
    {
        if ($this->subCategories->contains($subCategory)) {
            $this->subCategories->removeElement($subCategory);
            // set the owning side to null (unless already changed)
            if ($subCategory->getTopCategory() === $this) {
                $subCategory->setTopCategory(null);
            }
        }

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
