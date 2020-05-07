<?php

namespace App\DependencyInjection\Compiler;

use App\Services\FileParser\FileParserRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FileParserCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition(FileParserRegistry::class);
        $fileParser = $container->findTaggedServiceIds('app.file_parser');

        foreach ($fileParser as $id => $tags) {
            $definition->addMethodCall('addFileParser', [new Reference($id)]);
        }
    }
}
