<?php

namespace App\Tests\Services;

use App\Services\ConnectionKeeper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConnectionKeeperTest extends KernelTestCase
{
    private Connection $connection;

    private ConnectionKeeper $connectionKeeper;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $this->connection = $this->createOwnConnection();
        $this->connectionKeeper = new ConnectionKeeper($this->connection);
    }

    protected function tearDown(): void
    {
        $this->connection->close();

        parent::tearDown();
    }

    public function testALiveConnectionIsLeftAlone(): void
    {
        $threadId = $this->getThreadId();

        $this->assertTrue($this->connectionKeeper->isAlive());

        $this->connectionKeeper->keepAlive();

        $this->assertSame($threadId, $this->getThreadId(), 'The connection was needlessly reopened');
    }

    public function testReconnectingOpensANewConnection(): void
    {
        $threadId = $this->getThreadId();

        $this->connectionKeeper->reconnect();

        $this->assertNotSame($threadId, $this->getThreadId(), 'The connection was not reopened');
    }

    public function testKeepingAliveAnUnreachableServerDoesNotBlowUp(): void
    {
        // Nothing to reconnect to: reconnect() is only ever reached in that
        // case, doctrine closes the connection itself when it merely drops.
        $connectionKeeper = new ConnectionKeeper($this->createUnreachableConnection());

        $connectionKeeper->keepAlive();

        $this->assertFalse($connectionKeeper->isAlive());
    }

    public function testAConnectionDroppedByTheServerIsReopened(): void
    {
        // What happens to the web socket server when mysql closes a connection
        // left idle for longer than its wait_timeout.
        $threadId = $this->getThreadId();
        $this->killConnection($threadId);

        $this->assertFalse($this->connectionKeeper->isAlive(), 'The dropped connection went unnoticed');

        $this->connectionKeeper->keepAlive();

        $this->assertTrue($this->connectionKeeper->isAlive());
        $this->assertNotSame($threadId, $this->getThreadId(), 'The connection was not reopened');
    }

    /**
     * A connection of our own: killing the one the test suite shares would
     * break the transaction it rolls back after each test.
     */
    private function createOwnConnection(): Connection
    {
        return DriverManager::getConnection($this->getSharedConnection()->getParams());
    }

    private function createUnreachableConnection(): Connection
    {
        $params = $this->getSharedConnection()->getParams();
        $params['host'] = '127.0.0.1';
        $params['port'] = 1;

        return DriverManager::getConnection($params);
    }

    private function getSharedConnection(): Connection
    {
        return static::getContainer()->get(EntityManagerInterface::class)->getConnection();
    }

    private function getThreadId(): int
    {
        return (int) $this->connection->fetchOne('SELECT CONNECTION_ID()');
    }

    /**
     * Drop the connection the way the mysql server would, from another
     * connection. KILL is not transactional, so it leaves the shared
     * connection it is sent on untouched.
     */
    private function killConnection(int $threadId): void
    {
        $this->getSharedConnection()->executeStatement(sprintf('KILL %d', $threadId));

        // Killing is asynchronous: give the server the time to close the socket
        usleep(200000);
    }
}
