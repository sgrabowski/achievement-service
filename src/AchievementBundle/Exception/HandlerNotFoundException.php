<?php

namespace App\AchievementBundle\Exception;

use Exception;

class HandlerNotFoundException extends Exception
{
    public function __construct($tag)
    {
        parent::__construct(sprintf('No handler for tag %s is registered', $tag));
    }
}
