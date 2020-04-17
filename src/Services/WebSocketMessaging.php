<?php

namespace App\Services;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WebSocketMessaging implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $conn)
    {
        $conn->send(sprintf('New connection: #%d', $conn->resourceId));
    }

    public function onClose(ConnectionInterface $closedConnection)
    {
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->send('An error has occurred: ' . $e->getMessage());
        $conn->close();
    }

    public function onMessage(ConnectionInterface $conn, $message)
    {
        $conn->send($message);
    }
}
