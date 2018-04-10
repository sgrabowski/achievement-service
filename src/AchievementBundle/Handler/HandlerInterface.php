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
     */
    public function updateProgress(ProgressUpdateEvent $e): void;

    /**
     * Was the achievement completed after processing the update?
     *
     * @return bool
     */
    public function isAchieved(): bool;

    /**
     * Returns validation rules for the update payload ( @see ProgressUpdateEvent::$payload )
     * If null, no validation will be performed
     *
     * @return Constraint|null
     */
    public function getValidationConstraint(): ?Constraint;

    /**
     * Returns progress rate as percentage (0-100 float)
     *
     * Note: float is used here to allow displaying precise values, such as 33.23% completion
     *
     * @return float
     */
    public function getProgress(): float;
}