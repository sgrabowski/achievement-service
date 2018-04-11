<?php

namespace App\AchievementBundle\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public final function updateProgress(ProgressUpdateEvent $e): bool
    {
        $this->validatePayload($e);
        return $this->process($e->getPayload());
    }

    /**
     * Processes event data to see if achievement requirements are met
     *
     * @param $eventData
     * @return bool True if achievement is complete
     */
    protected abstract function process($eventData): bool;
}
