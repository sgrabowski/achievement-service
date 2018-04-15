<?php

namespace App\AchievementBundle\Service;

use App\AchievementBundle\Event\AchievementCompletedEvent;
use App\AchievementBundle\Event\AchievementProgressedEvent;
use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Event\ProgressUpdateUnhandledEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Exception\PayloadValidationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Processor implements EventSubscriberInterface
{

    protected $handlers;
    protected $eventDispatcher;
    protected $completionStorage;

    function __construct(HandlerMap $handlers, EventDispatcherInterface $eventDispatcher, CompletionStorage $completionStorage)
    {
        $this->handlers = $handlers;
        $this->eventDispatcher = $eventDispatcher;
        $this->completionStorage = $completionStorage;
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
     * @throws PayloadValidationException if a PayloadValidatingHandler is used
     * @param ProgressUpdateEvent $e
     */
    public function processEvent(ProgressUpdateEvent $e)
    {
        try {
            $handlers = $this->handlers->getHandlers($e);
        } catch (HandlerNotFoundException $exception) {
            $unhandledEvent = new ProgressUpdateUnhandledEvent($exception->getProgressUpdateEvent(), [
                "message" => $exception->getMessage()
            ]);
            $this->eventDispatcher->dispatch($unhandledEvent::NAME, $unhandledEvent);
            return;
        }


        foreach ($handlers as $handler) {
            if($this->completionStorage->isCompleted($handler->getAchievementId(), $e->getUserId())) {
                continue;
            }

            try {
                $achieved = $handler->updateProgress($e);
            } catch(PayloadValidationException $exception) {
                $unhandledEvent = new ProgressUpdateUnhandledEvent($exception->getProgressUpdateEvent(), [
                    'message' => $exception->getMessage(),
                    "validationErrors" => $exception->getPrettyValidationErrors()
                ]);
                $this->eventDispatcher->dispatch($unhandledEvent::NAME, $unhandledEvent);
                continue;
            }

            if ($achieved) {
                $completionEvent = new AchievementCompletedEvent($handler->getAchievementId(), $e->getUserId());
                $this->eventDispatcher->dispatch($completionEvent::NAME, $completionEvent);
                $this->completionStorage->markAsComplete($handler->getAchievementId(), $e->getUserId());
            } else {
                $updateEvent = new AchievementProgressedEvent($handler->getAchievementId(), $e->getUserId(), $handler->getProgress());
                $this->eventDispatcher->dispatch($updateEvent::NAME, $updateEvent);
            }
        }
    }

}
