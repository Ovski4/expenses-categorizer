<?php

namespace App\Services;

use App\Services\WebSocketMessageHandler\WebSocketMessageHandlerRegistry;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;

class WebSocketMessaging implements MessageComponentInterface
{
    private $loop;
    private $handlerRegistry;

    public function __construct(WebSocketMessageHandlerRegistry $handlerRegistry)
    {
        $this->handlerRegistry = $handlerRegistry;
    }

    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function onOpen(ConnectionInterface $conn) {}

    public function onClose(ConnectionInterface $closedConnection)
    {
        foreach($this->handlerRegistry->getHandlers() as $handler) {
            $handler->detachClients($closedConnection);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->send('An error has occurred: ' . $e->getMessage());
        $conn->close();
    }

    public function onMessage(ConnectionInterface $conn, $message)
    {
        $this->handlerRegistry->getHandler($message)->handle($conn, $this->loop);
    }
}
