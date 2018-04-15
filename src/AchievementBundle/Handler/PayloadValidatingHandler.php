<?php

namespace App\AchievementBundle\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\PayloadValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraint;

abstract class PayloadValidatingHandler implements HandlerInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * PayloadValidatingHandler constructor.
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validates payload data of the progression event
     *
     * @param ProgressUpdateEvent $e
     * @throws PayloadValidationException
     */
    public final function validatePayload(ProgressUpdateEvent $e): void
    {
        //@TODO: always allow extra fields
        $errors = $this->validator->validate($e->getPayload(), $this->getValidationConstraint($e->getTag()));

        if (count($errors) > 0) {
            throw new PayloadValidationException($e, $errors);
        }
    }

    /**
     * Returns validation rules for the update payload by event's tag ( @see ProgressUpdateEvent::$payload )
     * If null, no validation will be performed
     *
     * @param $tag string event tag
     * @return Constraint|null
     */
    public abstract function getValidationConstraint($tag): ?Constraint;
}