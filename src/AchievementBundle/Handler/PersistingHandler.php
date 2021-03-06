<?php

namespace App\AchievementBundle\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Service\MetadataStorage;
use Symfony\Component\Validator\Validator\ValidatorInterface;

//@TODO: make this handler sharable (progress -> progressMap)
abstract class PersistingHandler extends PayloadValidatingHandler
{
    /**
     * @var MetadataStorage
     */
    private $progressStorage;

    /**
     * @var float
     */
    private $progress = 0.0;

    /**
     * @param MetadataStorage $progressStorage
     * @param ValidatorInterface $validator
     */
    public function __construct(MetadataStorage $progressStorage, ValidatorInterface $validator)
    {
        parent::__construct($validator);
        $this->progressStorage = $progressStorage;
    }

    public function getProgress(): float
    {
        //make sure this never exceeds 100% unless overridden
        return min((float)100, $this->progress);
    }

    /**
     * {@inheritdoc}
     * @throws \App\AchievementBundle\Exception\PayloadValidationException
     */
    public final function updateProgress(ProgressUpdateEvent $e): bool
    {
        $this->validatePayload($e);
        $progressData = $this->progressStorage->retrieve($this->getAchievementId(), $e->getUserId());

        if($progressData === null) {
            $progressData = $this->initProgressData();
        }

        $processedData = $this->process($e->getTag(), $e->getPayload(), $progressData);
        $this->setInternalProgress($processedData);
        $this->progressStorage->store($this->getAchievementId(), $e->getUserId(), $processedData);

        return $this->progress >= 100;
    }

    /**
     * Process achievement progress data according to a received update
     *
     * @param $tag event tag
     * @param $eventData progress update data
     * @param $progressData progress data retrieved from the data storage, keep in mind this can be null
     * @return mixed updated progress data to be persisted in the data storage
     */
    protected abstract function process($tag, $eventData, $progressData);

    /**
     * Returns a freshly initialized progress data structure
     * This will be used when achievement is processed for the first time
     *
     * @return mixed
     */
    protected abstract function initProgressData();

    private function setInternalProgress($processedData)
    {
        $this->progress = $this->calculateProgress($processedData);
    }

    /**
     * Calculates the achievement progress, expressed as percantage - float number ranging from 0 to 100
     * If the progress cannot be calculated, this still needs to return 100 when achievement is complete
     *
     * @param $processedData progress data already updated by the process method
     * @return float progress percentage
     */
    protected abstract function calculateProgress($processedData): float;

    public function isSharable(): bool
    {
        return false;
    }
}
