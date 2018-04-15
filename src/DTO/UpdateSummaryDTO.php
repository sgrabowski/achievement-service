<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class UpdateSummaryDTO
{
    /**
     * @Serializer\Type("array<string, App\DTO\AchievementUpdateDTO>")
     */
    public $updatedAchievements = [];

    /**
     * @Serializer\Type("array<App\DTO\UnhandledEventDTO>")
     */
    public $unhandledEvents = [];
}