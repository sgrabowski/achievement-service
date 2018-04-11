<?php

namespace App\AchievementBundle\Tests\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Handler\PersistingHandler;
use App\AchievementBundle\Service\MetadataStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersistingHandlerTest extends TestCase
{
    protected static $storageData;

    protected function setUp()
    {
        self::$storageData = null;
    }

    public function testPersistingAchievement()
    {
        $progressStorage = $this->getProgressStorageMock();
        $handler = $this->getPersistingAchievementHandlerMock($progressStorage);

        $progressStorage->expects($this->exactly(3))
            ->method("retrieve");

        $progressStorage->expects($this->exactly(3))
            ->method("store");

        $event = new ProgressUpdateEvent("persisting-1", 1, ['incrementation' => 1]);
        $achieved = $handler->updateProgress($event);
        $this->assertEquals(2, $handler->getProgress());
        $this->assertEquals(false, $achieved);

        $event = new ProgressUpdateEvent("persisting-1", 1, ['incrementation' => 10]);
        $achieved = $handler->updateProgress($event);
        $this->assertEquals(22, $handler->getProgress());
        $this->assertEquals(false, $achieved);

        $event = new ProgressUpdateEvent("persisting-1", 1, ['incrementation' => 100]);
        $achieved = $handler->updateProgress($event);
        $this->assertEquals(100, $handler->getProgress());
        $this->assertEquals(true, $achieved);
    }

    protected function getProgressStorageMock()
    {
        $mock = $this->createMock(MetadataStorage::class);

        $mock->method("store")->willReturnCallback(function($achievementId, $userId, $data){
           self::$storageData = $data;
           return true;
        });

        $mock->method("retrieve")->willReturnCallback(function($achievementId, $userId){
            return self::$storageData;
        });

        return $mock;
    }

    protected function getPersistingAchievementHandlerMock(MetadataStorage $storage)
    {
        $mock = $this->getMockBuilder(PersistingHandler::class)
            ->setMethodsExcept(['getProgress'])
            ->setConstructorArgs([
                $storage,
                $this->getValidatorMock()
            ])->getMock();

        $mock->method("calculateProgress")->willReturnCallback(function ($processedData) {
            return (float)($processedData['total'] / 50) * 100;
        });
        $mock->method("process")->willReturnCallback(function ($eventData, $progressData) use (&$achieved) {
            $incrementation = $eventData['incrementation'];

            if(empty($progressData)) {
                $progressData = ['total' => 0];
            }

            $newTotal = $progressData['total'] + $incrementation;

            return ["total" => $newTotal];
        });

        return $mock;
    }

    protected function getValidatorMock()
    {
        $mock = $this->createMock(ValidatorInterface::class);
        $mock->method("validate")
            ->willReturn(new ConstraintViolationList());
        return $mock;
    }
}