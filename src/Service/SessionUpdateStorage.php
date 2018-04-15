<?php

namespace App\Service;

use App\AchievementBundle\Event\AchievementCompletedEvent;
use App\AchievementBundle\Event\AchievementEventInterface;
use App\AchievementBundle\Event\AchievementProgressedEvent;
use App\AchievementBundle\Event\ProgressUpdateUnhandledEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionUpdateStorage implements EventSubscriberInterface
{
    protected $updates = [];
    protected $unhandledEvents = [];

    public static function getSubscribedEvents()
    {
        return [
            AchievementProgressedEvent::NAME => "registerUpdate",
            AchievementCompletedEvent::NAME => "registerUpdate",
            ProgressUpdateUnhandledEvent::NAME => "registerUnhandledEvent"
        ];
    }

    public function registerUpdate(AchievementEventInterface $e)
    {
        //overwrite old updates if there were any
        $this->updates[$e->getAchievementId()] = $e;
    }

    public function registerUnhandledEvent(ProgressUpdateUnhandledEvent $e)
    {
        $this->unhandledEvents[] = $e;
    }

    /**
     * @return AchievementEventInterface[]
     */
    public function getUpdates(): array
    {
        return $this->updates;
    }

    /**
     * @return ProgressUpdateUnhandledEvent[]
     */
    public function getUnhandledEvents(): array
    {
        return $this->unhandledEvents;
    }
}