<?php

namespace App\AchievementBundle\Service;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class MetadataCacheStorage implements MetadataStorage, CompletionStorage
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
    public function store($achievementId, $userId, $data): bool
    {
        $item = $this->cache->getItem($this->buildCacheKey($achievementId, $userId));
        $item->set($data);
        $item->tag(['achievement_' . $achievementId, "user_" . $userId]);
        return $this->cache->save($item);
    }

    protected function buildCacheKey($achievementId, $userId, $key = "data")
    {
        return "achievement." . $key . "_" . $achievementId . "_" . $userId;
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function retrieve($achievementId, $userId): bool
    {
        return $this->cache->getItem($this->buildCacheKey($achievementId, $userId))->get();
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteUserData($userId): bool
    {
        return $this->cache->invalidateTags(["user_" . $userId]);
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteAchievementData($achievementId): bool
    {
        return $this->cache->invalidateTags(["achievement_" . $achievementId]);
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete($achievementId, $userId): bool
    {
        return $this->cache->deleteItem($this->buildCacheKey($achievementId, $userId));
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function markAsComplete($achievementId, $userId): bool
    {
        $item = $this->cache->getItem($this->buildCacheKey($achievementId, $userId, "obtained"));
        $item->set(true);
        $item->tag(['achievement_' . $achievementId, "user_" . $userId]);
        return $this->cache->save($item);
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function isCompleted($achievementId, $userId): bool
    {
        return $this->cache->getItem($this->buildCacheKey($achievementId, $userId, "obtained"))->get() ?? false;
    }
}
