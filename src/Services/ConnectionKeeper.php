<?php

namespace App\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;

class ConnectionKeeper
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
        // Doctrine has no public connect() anymore: closing is enough, the
        // next query opens a new connection.
        $this->connection->close();
    }

    public function keepAlive(): void
    {
        if (!$this->isAlive()) {
            $this->reconnect();
        }
    }
}
