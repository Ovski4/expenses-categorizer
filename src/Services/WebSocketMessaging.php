<?php

namespace App\Services;

use App\Services\WebSocketMessageHandler\WebSocketMessageHandlerRegistry;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;

class WebSocketMessaging implements MessageComponentInterface
{
    private LoopInterface $loop;
    private WebSocketMessageHandlerRegistry $handlerRegistry;

    public function __construct(WebSocketMessageHandlerRegistry $handlerRegistry)
    {
        $this->handlerRegistry = $handlerRegistry;
    }

    public function setLoop(LoopInterface $loop): void
    {
        $this->loop = $loop;
    }

    public function onOpen(ConnectionInterface $conn): void
    {
    }

    public function onClose(ConnectionInterface $closedConnection): void
    {
        foreach ($this->handlerRegistry->getHandlers() as $handler) {
            $handler->detachClients($closedConnection);
        }
    }

    public function onError(ConnectionInterface $conn, \Throwable $e): void
    {
        $conn->send('An error has occurred: '.$e->getMessage());
        $conn->close();
    }

    public function onMessage(ConnectionInterface $conn, $message): void
    {
        $this->handlerRegistry->getHandler($message)->handle($conn, $this->loop);
    }
}
