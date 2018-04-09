<?php

namespace App\AchievementBundle\Model;

class AchievementProgress
{

    /**
     * @var string
     */
    protected $achievementId;

    /**
     * Progress value - percentage
     * 
     * @var float
     */
    protected $progress;

    /**
     * Arbitrary progress data which can supplement the progress value
     * e.g. to display a particular value instead of percentage only
     * 
     * @var mixed
     */
    protected $data;

    public function __construct($achievementId, float $progress = 0, $data = null)
    {
        $this->achievementId = $achievementId;
        $this->progress = $progress;
        $this->data = $data;
    }

    public function setProgress(float $progress)
    {
        $this->progress = $progress;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getAchievementId()
    {
        return $this->achievementId;
    }

    public function getProgress(): float
    {
        return $this->progress;
    }

    public function getData()
    {
        return $this->data;
    }

}
