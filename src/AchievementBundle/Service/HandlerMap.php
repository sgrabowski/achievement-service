<?php

namespace App\AchievementBundle\Service;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Handler\Handler;

class HandlerMap
{
    private $handlers = [];
    
    public function registerHandler(Handler $handler)
    {
        $this->handlers[$handler->getAchievementId()] = $handler;
    }

    /**
     * Find achievement handler for update event
     * 
     * @param ProgressUpdateEvent $e
     * @return Handler
     * @throws HandlerNotFoundException
     */
    public function getHandler(ProgressUpdateEvent $e)
    {
        if(array_key_exists($e->getAchievementId(), $this->handlers)) {
            return $this->handlers[$e->getAchievementId()];
        }

        throw new HandlerNotFoundException($e->getAchievementId());
    }

}
