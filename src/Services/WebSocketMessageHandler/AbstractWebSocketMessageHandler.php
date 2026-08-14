<?php

namespace App\Services\WebSocketMessageHandler;

use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

abstract class AbstractWebSocketMessageHandler
{
    protected \SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function detachClients(ConnectionInterface $closedConnection): void
    {
        if ($this->clients->contains($closedConnection)) {
            $this->clients->detach($closedConnection);
        }
    }

    public function handle(ConnectionInterface $connection, LoopInterface $loop): void
    {
        $this->clients->attach($connection);
        $this->doHandle($connection, $loop);
    }

    protected function sendMessage(ConnectionInterface $connection, string $topic, ?array $data = null): void
    {
        $connection->send(json_encode([
            'topic' => $topic,
            'data' => $data,
        ]));
    }

    abstract protected function doHandle(ConnectionInterface $connection, LoopInterface $loop): void;
}
