<?php

namespace App\Controller;

use App\AchievementBundle\Event\AchievementCompletedEvent;
use App\AchievementBundle\Event\AchievementProgressedEvent;
use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\DTO\AchievementUpdateDTO;
use App\DTO\UnhandledEventDTO;
use App\DTO\UpdateDTO;
use App\DTO\UpdateSummaryDTO;
use App\Exception\ValidationException;
use App\Service\SessionUpdateStorage;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Rest\Route("/events")
 */
class EventController extends FOSRestController
{
    protected $eventDispatcher;

    protected $updateStorage;

    public function __construct(EventDispatcherInterface $eventDispatcher, SessionUpdateStorage $updateStorage)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->updateStorage = $updateStorage;
    }

    /**
     * Creates and dispatches a new progress update event
     *
     * @Rest\Post()
     * @ParamConverter("updateDTO", converter="fos_rest.request_body")
     * @Rest\View(statusCode=200)
     *
     * @param UpdateDTO $updateDTO
     * @param ConstraintViolationListInterface $validationErrors
     * @return array achievement updates
     * @throws ValidationException
     */
    public function postEventAction(UpdateDTO $updateDTO, ConstraintViolationListInterface $validationErrors)
    {
        if ($validationErrors->count() > 0) {
            throw new ValidationException($validationErrors);
        }

        $progressEvent = new ProgressUpdateEvent($updateDTO->tag, $updateDTO->userId, $updateDTO->payload);

        if ($updateDTO->eventId !== null) {
            $progressEvent->setEventId($updateDTO->eventId);
        }

        $this->eventDispatcher->dispatch($progressEvent::NAME, $progressEvent);

        return $this->prepareSummaryDTO();
    }

    //@TODO: move this out of controller
    protected function prepareSummaryDTO()
    {
        $summary = new UpdateSummaryDTO();
        foreach ($this->updateStorage->getUpdates() as $update) {
            $achievementUpdateDTO = new AchievementUpdateDTO();
            $achievementUpdateDTO->achievementId = $update->getAchievementId();
            $achievementUpdateDTO->userId = $update->getUserId();

            if ($update instanceof AchievementProgressedEvent) {
                $achievementUpdateDTO->progress = $update->getProgress();
            }

            if ($update instanceof AchievementCompletedEvent) {
                $achievementUpdateDTO->completionDateTime = $update->getCompletionDateTime();
                $achievementUpdateDTO->progress = 100;
            }

            $summary->updatedAchievements[$update->getAchievementId()] = $achievementUpdateDTO;
        }

        foreach ($this->updateStorage->getUnhandledEvents() as $unhandledEvent) {
            $unhandledEventDTO = new UnhandledEventDTO();
            $cause = $unhandledEvent->getProgressUpdateEvent();

            $updateDTO = new UpdateDTO();
            $updateDTO->userId = $cause->getUserId();
            $updateDTO->tag = $cause->getTag();
            $updateDTO->eventId = $cause->getEventId();
            $updateDTO->payload = $cause->getPayload();

            $unhandledEventDTO->originalEvent = $updateDTO;
            $unhandledEventDTO->reason = $unhandledEvent->getReason();

            $summary->unhandledEvents[] = $unhandledEventDTO;
        }

        return $summary;
    }
}