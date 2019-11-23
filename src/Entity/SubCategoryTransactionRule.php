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
    private $sub_category;

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
        return $this->sub_category;
    }

    public function setSubCategory(?SubCategory $sub_category): self
    {
        $this->sub_category = $sub_category;

        return $this;
    }
}
