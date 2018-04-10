<?php

namespace App\AchievementBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AchievementCompletedEvent extends Event
{
    const NAME = "achievement.completed";

    /**
     * @var string
     */
    protected $achievementId;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var \DateTime
     */
    protected $completionDateTime;

    /**
     * AchievementCompletedEvent constructor.
     * @param string $achievementId
     * @param string $userId
     */
    public function __construct(string $achievementId, string $userId)
    {
        $this->achievementId = $achievementId;
        $this->userId = $userId;
        $this->completionDateTime = new \DateTime();
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
     * @return \DateTime
     */
    public function getCompletionDateTime(): \DateTime
    {
        return $this->completionDateTime;
    }
}