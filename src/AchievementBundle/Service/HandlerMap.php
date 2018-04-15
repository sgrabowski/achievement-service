<?php

namespace App\AchievementBundle\Service;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Handler\HandlerInterface;

class HandlerMap
{
    private $handlersByTag = [];

    public function registerHandler(HandlerInterface $handler)
    {
        $tags = $handler->getTriggeredByTags();
        foreach ($tags as $tag) {
            if(array_key_exists($tag, $this->handlersByTag)) {
                if(!in_array($handler, $this->handlersByTag[$tag])) {
                    $this->handlersByTag[$tag][] = $handler;
                }
            } else {
                $this->handlersByTag[$tag] = [$handler];
            }
        }
    }

    /**
     * Find achievement handler for update event
     *
     * @param ProgressUpdateEvent $e
     * @return HandlerInterface[]
     * @throws HandlerNotFoundException
     */
    public function getHandlers(ProgressUpdateEvent $e)
    {
        if (array_key_exists($e->getTag(), $this->handlersByTag)) {
            $handlers = $this->handlersByTag[$e->getTag()];
            $toReturn = [];
            foreach ($handlers as $handler) {
                if($handler->isSharable()) {
                    $toReturn[] = $handler;
                } else {
                    $toReturn[] = clone $handler;
                }
            }

            return $toReturn;
        }

        throw new HandlerNotFoundException($e);
    }

}
