<?php

namespace App\AchievementBundle\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use Symfony\Component\Validator\Constraint;

abstract class Handler
{
    public abstract function getAchievementId();
    public abstract function isAchieved(): bool;
    public abstract function process(ProgressUpdateEvent $e);
    public abstract function getValidationConstraint(): ?Constraint;
}
