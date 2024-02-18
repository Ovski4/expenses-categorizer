<?php

namespace App\DependencyInjection\Compiler;

use App\Services\WebSocketMessageHandler\WebSocketMessageHandlerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WebSocketMessageHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition(WebSocketMessageHandlerRegistry::class);
        $handlers = $container->findTaggedServiceIds('app.message_handler');

        foreach ($handlers as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['trigger'])) {
                    throw new \Exception(sprintf(
                        'Service with id "%s" tagged as "app.message_handler" must have its trigger attribute defined',
                        $id
                    ));
                }

                $definition->addMethodCall('setHandler', [$attributes['trigger'], new Reference($id)]);
            }
        }
    }
}
