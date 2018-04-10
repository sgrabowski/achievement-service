<?php

namespace App\AchievementBundle\DependencyInjection;

use App\AchievementBundle\Handler\HandlerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AchievementExtension extends Extension
{

    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(HandlerInterface::class)
            ->addTag("achievement.progress_handler");
    }
}