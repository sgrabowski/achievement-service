<?php

namespace App\AchievementBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AchievementProgressedEvent extends Event
{
    const NAME = "achievement.progressed";

    /**
     * @var string
     */
    protected $achievementId;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var float
     */
    protected $progress;

    /**
     * @param string $achievementId
     * @param string $userId
     * @param float $progress
     */
    public function __construct(string $achievementId, string $userId, float $progress)
    {
        $this->achievementId = $achievementId;
        $this->userId = $userId;
        $this->progress = $progress;
    }

    /**
     * @return string
     */
    public function getAchievementId(): string
    {
        return $this->achievementId;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return float
     */
    public function getProgress(): float
    {
        return $this->progress;
    }
}