<?php

namespace App\Tests\Controller\Fixtures;

use App\AchievementBundle\Handler\PersistingHandler;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

class KillAndDestroyAchievementHandler extends PersistingHandler
{

    public function getAchievementId(): string
    {
        return "kill-and-destroy";
    }

    public function getValidationConstraint($tag): ?Constraint
    {
        if ($tag == "player-killed") {
            return new Assert\Collection([
                "allowExtraFields" => true,
                "fields" => [
                    "time" => new Assert\DateTime()
                ]
            ]);
        }

        if ($tag == "structure-destroyed") {
            return new Assert\Collection([
                "allowExtraFields" => true,
                "fields" => [
                    "time" => new Assert\DateTime()
                ]
            ]);
        }
    }

    public function getTriggeredByTags(): array
    {
        return ['player-killed', 'structure-destroyed'];
    }

    protected function calculateProgress($processedData): float
    {
        if (!empty($processedData['lastPlayerKilledAt']) && !empty($processedData['lastStructureDestroyedAt'])) {
            $date1 = new \DateTime($processedData['lastPlayerKilledAt']);
            $date2 = new \DateTime($processedData['lastStructureDestroyedAt']);
            $diff = $date1->diff($date2, true);
            if ($diff->s <= 60 && $diff->h < 1 && $diff->days < 1) {
                return 100;
            }
        }

        return 0;
    }

    protected function process($tag, $eventData, $progressData)
    {
        if ($tag == "player-killed") {
            $progressData['lastPlayerKilledAt'] = $eventData["time"];
        }

        if ($tag == "structure-destroyed") {
            $progressData['lastStructureDestroyedAt'] = $eventData["time"];
        }

        return $progressData;
    }

    protected function initProgressData()
    {
        return [
            'lastPlayerKilledAt' => null,
            'lastStructureDestroyedAt' => null
        ];
    }
}