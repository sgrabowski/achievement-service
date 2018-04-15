<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class AchievementUpdateDTO
{
    /**
     * @Serializer\Type("string")
     */
    public $achievementId;

    /**
     * @Serializer\Type("string")
     */
    public $userId;

    /**
     * @Serializer\Type("DateTime<'Y-m-d H:i:s'>")
     */
    public $completionDateTime;

    /**
     * @Serializer\Type("float")
     */
    public $progress;
}