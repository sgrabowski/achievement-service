<?php

namespace App\AchievementBundle\Exception;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class PayloadValidationException extends Exception
{

    /**
     * @var ProgressUpdateEvent
     */
    protected $event;

    /**
     * @var ConstraintViolationListInterface
     */
    protected $validationErrors;

    public function __construct(ProgressUpdateEvent $event, ConstraintViolationListInterface $validationErrors)
    {
        $this->event = $event;
        $this->validationErrors = $validationErrors;
    }

    public function getEvent(): ProgressUpdateEvent
    {
        return $this->event;
    }

    public function getValidationErrors(): ConstraintViolationListInterface
    {
        return $this->validationErrors;
    }

}
