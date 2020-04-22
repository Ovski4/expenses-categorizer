<?php

namespace App\Services\WebSocketMessageHandler;

use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

abstract class AbstractWebSocketMessageHandler
{
    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function detachClients($closedConnection)
    {
        if ($this->clients->contains($closedConnection)) {
            $this->clients->detach($closedConnection);
        }
    }

    public function handle(ConnectionInterface $connection, LoopInterface $loop)
    {
        $this->clients->attach($connection);
        $this->doHandle($connection, $loop);
    }

    protected function sendMessage(ConnectionInterface $connection, $topic, $data = null)
    {
        $connection->send(json_encode([
            'topic' => $topic,
            'data' => $data
        ]));
    }

    abstract protected function doHandle(ConnectionInterface $connection, LoopInterface $loop);
}
