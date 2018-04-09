<?php

namespace App\AchievementBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

//TODO: add exception handling
class HandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds('achievement.progress_handler');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition = $container->getDefinition("achievement.handler_map");

                $definition->addMethodCall('registerHandler', array(
                    new Reference($id)
                ));
            }
        }
    }
}