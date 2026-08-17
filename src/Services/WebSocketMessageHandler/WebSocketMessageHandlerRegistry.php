<?php

namespace App\Services\WebSocketMessageHandler;

class WebSocketMessageHandlerRegistry
{
    /**
     * @var AbstractWebSocketMessageHandler[]
     */
    private array $handlers;

    public function __construct()
    {
        $this->handlers = [];
    }

    public function setHandler(string $trigger, AbstractWebSocketMessageHandler $handler): self
    {
        $this->handlers[$trigger] = $handler;

        return $this;
    }

    public function getHandler(string $trigger): AbstractWebSocketMessageHandler
    {
        if (!isset($this->handlers[$trigger])) {
            throw new \InvalidArgumentException(sprintf('Could not load handler "%s". Available handlers are %s', $trigger, implode(', ', array_keys($this->handlers))));
        }

        return $this->handlers[$trigger];
    }

    /**
     * @return AbstractWebSocketMessageHandler[]
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
