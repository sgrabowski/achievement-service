<?php

namespace App\Tests\Controller\Fixtures;

use App\AchievementBundle\Handler\InstantHandler;
use Symfony\Component\Validator\Constraint;

class FirstBloodAchievementHandler extends InstantHandler
{
    public function getAchievementId(): string
    {
        return "first-blood";
    }

    public function getValidationConstraint($tag): ?Constraint
    {
        return null;
    }

    public function getProgress(): ?float
    {

    }

    public function getTriggeredByTags(): array
    {
        return ['player-killed'];
    }

    public function isSharable(): bool
    {
        return false;
    }

    protected function process($tag, $eventData): bool
    {
        return true;
    }
}