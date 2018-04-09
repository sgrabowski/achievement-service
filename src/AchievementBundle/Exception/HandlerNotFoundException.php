<?php

namespace App\AchievementBundle\Exception;

use Exception;

class HandlerNotFoundException extends Exception
{
    public function __construct($achievementId)
    {
        parent::__construct(sprintf('Handler for achievement %s is not registered', $achievementId));
    }
}
