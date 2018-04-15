<?php

namespace App\AchievementBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ProgressUpdateUnhandledEvent extends Event
{
    const NAME = "achievement.progress_update.unhandled";

    /**
     * @var ProgressUpdateEvent
     */
    protected $progressUpdateEvent;

    /**
     * @var mixed any data that helps identifying the problem
     */
    protected $reason;

    public function __construct(ProgressUpdateEvent $progressUpdateEvent, $reason)
    {
        $this->progressUpdateEvent = $progressUpdateEvent;
        $this->reason = $reason;
    }

    /**
     * @return ProgressUpdateEvent
     */
    public function getProgressUpdateEvent(): ProgressUpdateEvent
    {
        return $this->progressUpdateEvent;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

}