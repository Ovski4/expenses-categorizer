<?php

namespace App\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;

class ConnectionKeeper
{
    private ?Connection $connection = null;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->connection = $entityManager->getConnection();
    }

    public function isAlive(): bool
    {
        try {
            $dummySelectQuery = $this->connection->getDatabasePlatform()->getDummySelectSQL();
            $this->connection->executeQuery($dummySelectQuery);

            return true;
        } catch (DBALException $e) {
            return false;
        }
    }

    public function reconnect(): void
    {
        $this->connection->close();
        $this->connection->connect();
    }

    public function keepAlive(): void
    {
        if(!$this->isAlive()) {
            $this->reconnect();
        }
    }
}
