<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateDTO
{
    /**
     * @Serializer\Type("string")
     *
     * @Assert\NotBlank(message="Event tag is required")
     */
    public $tag;

    /**
     * @Serializer\Type("string")
     *
     * @Assert\NotBlank(message="User id is required")
     */
    public $userId;

    /**
     * @Serializer\Type("array")
     */
    public $payload;

    /**
     * @Serializer\Type("string")
     */
    public $eventId;
}