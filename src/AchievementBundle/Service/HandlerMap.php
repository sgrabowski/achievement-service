<?php

namespace App\AchievementBundle\Service;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Handler\HandlerInterface;

class HandlerMap
{
    private $handlers = [];

    public function registerHandler(HandlerInterface $handler)
    {
        $this->handlers[$handler->getAchievementId()] = $handler;
    }

    /**
     * Find achievement handler for update event
     *
     * @param ProgressUpdateEvent $e
     * @return HandlerInterface
     * @throws HandlerNotFoundException
     */
    public function getHandler(ProgressUpdateEvent $e)
    {
        if (array_key_exists($e->getAchievementId(), $this->handlers)) {
            return $this->handlers[$e->getAchievementId()];
        }

        throw new HandlerNotFoundException($e->getAchievementId());
    }

}
