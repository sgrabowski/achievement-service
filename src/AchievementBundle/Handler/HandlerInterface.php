<?php

namespace App\AchievementBundle\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use Symfony\Component\Validator\Constraint;

interface HandlerInterface
{
    /**
     * Returns identifier of the achievement this handler handles
     *
     * @return string
     */
    public function getAchievementId(): string;

    /**
     * Updates achievement progress
     *
     * @param ProgressUpdateEvent $e
     * @return bool True if achievement is complete
     */
    public function updateProgress(ProgressUpdateEvent $e): bool;

    /**
     * Returns validation rules for the update payload by event's tag ( @see ProgressUpdateEvent::$payload )
     * If null, no validation will be performed
     *
     * @param $tag string event tag
     * @return Constraint|null
     */
    public function getValidationConstraint($tag): ?Constraint;

    /**
     * Returns progress rate as percentage (0-100 float)
     *
     * Note: float is used here to allow displaying precise values, such as 33.23% completion
     *
     * @return float
     */
    public function getProgress(): float;

    /**
     * Returns a list of event tags defining which events trigger this handler to process them
     *
     * @return array|string[]
     */
    public function getTriggeredByTags(): array;

    /**
     * Returns whether a single instance of this handler can be used for all handled events
     * In other words, returns whether this handler is stateless or not
     *
     * @return bool
     */
    public function isSharable(): bool;
}