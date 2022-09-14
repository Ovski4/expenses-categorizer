<?php

namespace App\Entity;

class DecoratedTransaction
{
    private ?Transaction $transaction = null;

    private bool $exists = false;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function getLabel(): ?string
    {
        return $this->transaction->getLabel();
    }

    public function getAmount(): ?float
    {
        return $this->transaction->getAmount();
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->transaction->getCreatedAt();
    }

    public function getAccount(): ?Account
    {
        return $this->transaction->getAccount();
    }

    public function setExists(bool $exists): self
    {
        $this->exists = $exists;

        return $this;
    }

    public function exists(): bool
    {
       return $this->exists;
    }
}
