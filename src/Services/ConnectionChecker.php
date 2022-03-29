<?php

namespace App\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;

class ConnectionChecker
{
    public function isAlive(Connection $connection): bool
    {
        try {
            $connection->query($connection->getDatabasePlatform()->getDummySelectSQL());

            return true;
        } catch (DBALException $e) {
            return false;
        }
    }
}
