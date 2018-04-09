<?php

namespace App\AchievementBundle\Model;

class Achievement
{

    protected $id;
    protected $name;
    protected $description;
    protected $icon;

    public function __construct($id, $name, $description, $icon)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->icon = $icon;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getIcon()
    {
        return $this->icon;
    }

}
