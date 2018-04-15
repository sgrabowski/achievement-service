<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \Exception
{

    private $validationErrors;

    public function __construct(ConstraintViolationListInterface $validationErrors)
    {
        $this->validationErrors = $validationErrors;
    }

    /**
     * Returns formatted errors
     *
     * @return array
     */
    public function getValidationErrors()
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
