<?php

namespace App\AchievementBundle\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;

abstract class InstantHandler extends PayloadValidatingHandler
{
    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        parent::__construct($validator);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\AchievementBundle\Exception\PayloadValidationException
     */
    public final function updateProgress(ProgressUpdateEvent $e): void
    {
        $this->validatePayload($e);
        $this->process($e->getPayload());
    }

    /**
     * Processes event data to see if achievement requirements are met
     * This method should change the object's state so that isAchieved() returns accordingly
     *
     * @param $eventData
     */
    protected abstract function process($eventData): void;

    public function getProgress(): float
    {
        return (float) $this->isAchieved() ? 100 : 0;
    }
}
