<?php

namespace App\AchievementBundle\Tests\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Handler\PersistingHandler;
use App\AchievementBundle\Service\ProgressStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersistingHandlerTest extends TestCase
{
    protected static $storageData;

    public function testPersistingAchievement()
    {
        $progressStorage = $this->getProgressStorageMock();
        $handler = $this->getPersistingAchievementHandlerMock($progressStorage);

        $progressStorage->expects($this->exactly(3))
            ->method("retrieve");

        $progressStorage->expects($this->exactly(3))
            ->method("store");

        $event = new ProgressUpdateEvent("persisting-1", 1, ['incrementation' => 1]);
        $handler->updateProgress($event);
        $this->assertEquals(2, $handler->getProgress());
        $this->assertEquals(false, $handler->isAchieved());

        $event = new ProgressUpdateEvent("persisting-1", 1, ['incrementation' => 10]);
        $handler->updateProgress($event);
        $this->assertEquals(22, $handler->getProgress());
        $this->assertEquals(false, $handler->isAchieved());

        $event = new ProgressUpdateEvent("persisting-1", 1, ['incrementation' => 100]);
        $handler->updateProgress($event);
        $this->assertEquals(100, $handler->getProgress());
        $this->assertEquals(true, $handler->isAchieved());
    }

    protected function getProgressStorageMock()
    {
        $mock = $this->createMock(ProgressStorage::class);

        $mock->method("store")->willReturnCallback(function($achievementId, $userId, $data){
           self::$storageData = $data;
        });

        $mock->method("retrieve")->willReturnCallback(function($achievementId, $userId){
            if(empty(self::$storageData)) {
                self::$storageData = ['total' => 0];
            }
            return self::$storageData;
        });

        return $mock;
    }

    protected function getPersistingAchievementHandlerMock(ProgressStorage $storage)
    {
        $mock = $this->getMockBuilder(PersistingHandler::class)
            ->setMethodsExcept(['getProgress', 'isAchieved'])
            ->setConstructorArgs([
                $storage,
                $this->getValidatorMock()
            ])->getMock();

        $mock->method("calculateProgress")->willReturnCallback(function ($processedData) {
            return (float)($processedData['total'] / 50) * 100;
        });
        $mock->method("process")->willReturnCallback(function ($eventData, $progressData) use (&$achieved) {
            $incrementation = $eventData['incrementation'];
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