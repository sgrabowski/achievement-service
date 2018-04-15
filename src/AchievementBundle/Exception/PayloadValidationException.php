<?php

namespace App\AchievementBundle\Exception;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use Exception;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class PayloadValidationException extends Exception
{

    /**
     * @var ProgressUpdateEvent
     */
    protected $progressUpdateEvent;

    /**
     * @var ConstraintViolationListInterface
     */
    protected $validationErrors;

    public function __construct(ProgressUpdateEvent $progressUpdateEvent, ConstraintViolationListInterface $validationErrors)
    {
        $this->progressUpdateEvent = $progressUpdateEvent;
        $this->validationErrors = $validationErrors;
        parent::__construct("Invalid payload");
    }

    public function getProgressUpdateEvent(): ProgressUpdateEvent
    {
        return $this->progressUpdateEvent;
    }

    public function getValidationErrors(): ConstraintViolationListInterface
    {
        return $this->validationErrors;
    }

    /**
     * Returns formatted errors
     *
     * @return array
     */
    public function getPrettyValidationErrors()
    {
        $errors = [];

        foreach ($this->validationErrors as $validationError) {
            /* @var $validationError ConstraintViolationInterface */
            $propertyPath = $validationError->getPropertyPath();

            $message = $validationError->getMessage();

            $errors[$propertyPath] = $message;
        }

        return $errors;
    }
}
