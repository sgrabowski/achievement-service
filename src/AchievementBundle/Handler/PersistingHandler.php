<?php

namespace App\AchievementBundle\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Service\ProgressStorage;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class PersistingHandler extends PayloadValidatingHandler
{
    /**
     * @var ProgressStorage
     */
    private $progressStorage;

    /**
     * @var float
     */
    private $progress = 0.0;

    /**
     * @param ProgressStorage $progressStorage
     * @param ValidatorInterface $validator
     */
    public function __construct(ProgressStorage $progressStorage, ValidatorInterface $validator)
    {
        parent::__construct($validator);
        $this->progressStorage = $progressStorage;
    }

    public function getProgress(): float
    {
        //make sure this never exceeds 100% unless overridden
        return min((float)100, $this->progress);
    }

    private function setInternalProgress($processedData)
    {
        $this->progress = $this->calculateProgress($processedData);
    }

    protected abstract function calculateProgress($processedData): float;

    /**
     * {@inheritdoc}
     * @throws \App\AchievementBundle\Exception\PayloadValidationException
     */
    public final function updateProgress(ProgressUpdateEvent $e): void
    {
        $this->validatePayload($e);
        $progressData = $this->progressStorage->retrieve($e->getAchievementId(), $e->getUserId());
        $processedData = $this->process($e->getPayload(), $progressData);
        $this->setInternalProgress($processedData);
        $this->progressStorage->store($e->getAchievementId(), $e->getUserId(), $processedData);
    }

    protected abstract function process($eventData, $progressData);

    public function isAchieved(): bool
    {
        return $this->progress >= 100;
    }
}
