<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class UnhandledEventDTO
{
    /**
     * @Serializer\Type("App\DTO\UpdateDTO")
     */
    public $originalEvent;

    public $reason;
}