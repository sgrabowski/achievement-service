<?php

namespace App\AchievementBundle\Handler;

abstract class PersistingHandler extends Handler
{
    protected $progressData;

    function getProgressData()
    {
        return $this->progressData;
    }

    function setProgressData($progressData)
    {
        $this->progressData = $progressData;
    }

    public final function getProgress(): float
    {
        return min((float)100, $this->calculateProgress());
    }

    protected abstract function calculateProgress(): float;
}
