<?php

namespace App\AchievementBundle\Service;

interface CompletionStorage
{

    /**
     * Marks achievement as complete
     *
     * @param $achievementId
     * @param $userId
     * @return bool True if the item was successfully persisted. False if there was an error.
     */
    public function markAsComplete($achievementId, $userId): bool;

    /**
     * Checks if achievement is marked as complete
     *
     * @param $achievementId
     * @param $userId
     * @return bool
     */
    public function isCompleted($achievementId, $userId): bool;
}