<?php

namespace App\AchievementBundle\Exception;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use Exception;

class HandlerNotFoundException extends Exception
{
    protected $progressUpdateEvent;

    public function __construct(ProgressUpdateEvent $e)
    {
        $this->progressUpdateEvent = $e;
        parent::__construct(sprintf('No handler for tag "%s" is registered', $e->getTag()));
    }

    /**
     * @return ProgressUpdateEvent
     */
    public function getProgressUpdateEvent(): ProgressUpdateEvent
    {
        return $this->progressUpdateEvent;
    }

}
