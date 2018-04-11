<?php

namespace App\AchievementBundle\Service;

interface MetadataStorage
{
    /**
     * Stores user's achievement data
     *
     * @param string $achievementId
     * @param string $userId
     * @param mixed $data
     * @return bool True if the item was successfully persisted. False if there was an error.
     */
    public function store($achievementId, $userId, $data): bool;

    /**
     * Returns user's achievement data
     *
     * @param $achievementId
     * @param $userId
     * @return mixed|null Achievement data or null if it cannot be retrieved
     */
    public function retrieve($achievementId, $userId);

    /**
     * Remove all achievement data related to user
     *
     * @param $userId
     * @return bool True on success
     */
    public function deleteUserData($userId): bool;

    /**
     * Remove specific achievement data for all users
     *
     * @param $achievementId
     * @return bool True on success
     */
    public function deleteAchievementData($achievementId): bool;

    /**
     * Remove a single entry of user's achievement data
     *
     * @param $achievementId
     * @param $userId
     * @return bool True if the item was successfully removed. False if there was an error.
     */
    public function delete($achievementId, $userId): bool;
}