<?php

namespace App\AchievementBundle\Service;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Exception\PayloadValidationException;
use App\AchievementBundle\Handler\PersistingHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Processor implements EventSubscriberInterface
{

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var HandlerMap
     */
    protected $handlers;

    /**
     * @var ProgressStorage
     */
    protected $progressStorage;

    /**
     * @var Manager
     */
    protected $manager;

    function __construct(ValidatorInterface $validator, HandlerMap $handlers, ProgressStorage $progressStorage, Manager $manager)
    {
        $this->validator = $validator;
        $this->handlers = $handlers;
        $this->progressStorage = $progressStorage;
        $this->manager = $manager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProgressUpdateEvent::NAME => "processEvent"
        ];
    }

    /**
     * Process single achievement progress update
     * 
     * @throws HandlerNotFoundException
     * @throws PayloadValidationException
     * @param ProgressUpdateEvent $e
     */
    public function processEvent(ProgressUpdateEvent $e)
    {
        $handler = $this->handlers->getHandler($e);

        $errors = $this->validator->validate($e->getPayload(), $handler->getValidationConstraint());

        if (count($errors) > 0) {
            throw new PayloadValidationException($e, $errors);
        }

        if ($handler instanceof PersistingHandler) {
            $handler->setProgressData($this->progressStorage->retrieve($e->getAchievementId(), $e->getUserId()));
        }

        $handler->process($e);

        if ($handler instanceof PersistingHandler) {
            $this->progressStorage->store($e->getAchievementId(), $e->getUserId(), $handler->getProgressData());
            $this->manager->updateUserProgress($e->getAchievementId(), $e->getUserId(), $handler->getProgress());
        }

        if ($handler->isAchieved()) {
            $this->achieve($e);
        }
    }

    protected function achieve(ProgressUpdateEvent $e)
    {
        
    }

}
