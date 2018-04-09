<?php

namespace App\AchievementBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ProgressUpdateEvent extends Event
{

    const NAME = "achievement.progress_update";

    /**
     * @var string
     */
    protected $achievementId;

    /**
     * User to whom the update relates
     * 
     * @var string
     */
    protected $userId;

    /**
     * Arbitrary event data
     * 
     * @var mixed
     */
    protected $payload;

    /**
     * Optional eventId for debugging / rejection identification
     * 
     * @var string
     */
    protected $eventId;

    function __construct($achievementId, $userId, $payload)
    {
        $this->achievementId = $achievementId;
        $this->userId = $userId;
        $this->payload = $payload;
    }

    function getAchievementId()
    {
        return $this->achievementId;
    }

    function getUserId()
    {
        return $this->userId;
    }

    function getPayload()
    {
        return $this->payload;
    }

    function getEventId()
    {
        return $this->eventId;
    }

    function setEventId($eventId)
    {
        $this->eventId = $eventId;
    }

}
