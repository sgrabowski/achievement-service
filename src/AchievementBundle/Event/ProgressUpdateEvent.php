<?php

namespace App\AchievementBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ProgressUpdateEvent extends Event
{

    const NAME = "achievement.progress_update";

    protected $tag;

    protected $userId;

    protected $payload;

    /**
     * Optional eventId for debugging / rejection identification
     *
     * @var string
     */
    protected $eventId;

    /**
     * @param $tag string Event tag used by handles to check whether they should trigger event processing
     * @param $userId string User to whom the update relates
     * @param $payload mixed Arbitrary event data
     */
    function __construct($tag, $userId, $payload)
    {
        $this->tag = $tag;
        $this->userId = $userId;
        $this->payload = $payload;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    function getUserId(): string
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
