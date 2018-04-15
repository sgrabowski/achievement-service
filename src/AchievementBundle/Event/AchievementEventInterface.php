<?php

namespace App\AchievementBundle\Event;

interface AchievementEventInterface
{
    public function getAchievementId(): string;
    public function getUserId(): string;
}