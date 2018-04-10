<?php

namespace App\AchievementBundle\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\PayloadValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        $errors = $this->validator->validate($e->getPayload(), $this->getValidationConstraint());

        if (count($errors) > 0) {
            throw new PayloadValidationException($e, $errors);
        }
    }
}