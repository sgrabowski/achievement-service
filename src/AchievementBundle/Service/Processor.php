<?php

namespace App\AchievementBundle\Service;

use App\AchievementBundle\Event\AchievementCompletedEvent;
use App\AchievementBundle\Event\AchievementProgressedEvent;
use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Exception\PayloadValidationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Processor implements EventSubscriberInterface
{
    /**
     * @var HandlerMap
     */
    protected $handlers;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    function __construct(HandlerMap $handlers, EventDispatcherInterface $eventDispatcher)
    {
        $this->handlers = $handlers;
        $this->eventDispatcher = $eventDispatcher;
        $eventDispatcher->addSubscriber($this);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProgressUpdateEvent::NAME => "processEvent"
        ];
    }

    /**
     * Process single achievement progress update
     *
     * @throws HandlerNotFoundException
     * @throws PayloadValidationException if a PayloadValidatingHandler is used
     * @param ProgressUpdateEvent $e
     */
    public function processEvent(ProgressUpdateEvent $e)
    {
        $handler = $this->handlers->getHandler($e);

        $handler->updateProgress($e);

        if ($handler->isAchieved()) {
            $completionEvent = new AchievementCompletedEvent($e->getAchievementId(), $e->getUserId());
            $this->eventDispatcher->dispatch($completionEvent);
        } else {
            $updateEvent = new AchievementProgressedEvent($e->getAchievementId(), $e->getUserId(), $handler->getProgress());
            $this->eventDispatcher->dispatch($updateEvent::NAME, $updateEvent);
        }
    }

}
