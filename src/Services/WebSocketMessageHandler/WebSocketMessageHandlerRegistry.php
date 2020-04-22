<?php

namespace App\Services\WebSocketMessageHandler;

class WebSocketMessageHandlerRegistry
{
    private $handlers;

    public function __construct()
    {
        $this->handlers = [];
    }

    public function setHandler($name, AbstractWebSocketMessageHandler $handler)
    {
        $this->handlers[$name] = $handler;

        return $this;
    }

    public function getHandler($trigger)
    {
        if (!is_string($trigger)) {
            throw new \Exception(sprintf(
                'Expected argument of type "string", "%s" given',
                is_object($trigger) ? get_class($trigger) : gettype($trigger)
            ));
        }

        if (!isset($this->handlers[$trigger])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not load handler "%s". Available handlers are %s',
                $trigger,
                implode(', ', array_keys($this->handlers))
            ));
        }

        return $this->handlers[$trigger];
    }

    public function getHandlers()
    {
        return $this->handlers;
    }
}
