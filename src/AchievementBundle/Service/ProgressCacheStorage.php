<?php

namespace App\AchievementBundle\Service;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class ProgressCacheStorage implements ProgressStorageInterface
{
    private $cache;

    public function __construct(TagAwareAdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function store($achievementId, $userId, $data)
    {
        $item = $this->cache->getItem($this->buildCacheKey($achievementId, $userId));
        $item->set($data);
        $item->tag(['achievement_' . $achievementId, "user_" . $userId]);
        return $this->cache->save($item);
    }

    protected function buildCacheKey($achievementId, $userId)
    {
        return "achievement.data_" . $achievementId . "_" . $userId;
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function retrieve($achievementId, $userId)
    {
        return $this->cache->getItem($this->buildCacheKey($achievementId, $userId))->get();
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteUserData($userId)
    {
        return $this->cache->invalidateTags(["user_" . $userId]);
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteAchievementData($achievementId)
    {
        return $this->cache->invalidateTags(["achievement_" . $achievementId]);
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete($achievementId, $userId)
    {
        return $this->cache->deleteItem($this->buildCacheKey($achievementId, $userId));
    }
}
