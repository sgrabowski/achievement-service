<?php

namespace App\AchievementBundle\Tests\Service;

use App\AchievementBundle\Event\AchievementCompletedEvent;
use App\AchievementBundle\Event\AchievementProgressedEvent;
use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Handler\HandlerInterface;
use App\AchievementBundle\Service\CompletionStorage;
use App\AchievementBundle\Service\HandlerMap;
use App\AchievementBundle\Service\Processor;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcessorTest extends TestCase
{
    protected static $storageData;

    public function testAchievementProgressionAndCompletion()
    {
        $dispatcher = $this->getEventDispatcherMock();

        $handler = $this->createMock(HandlerInterface::class);
        $handler->method("getAchievementId")->willReturn("test-1");
        $handler->method("getValidationConstraint")->willReturn(null);
        $handler->method("getTriggeredByTags")->willReturn(['test-1', 'test2']);
        $handler->method("updateProgress")->willReturnOnConsecutiveCalls(false, true);

        $handler->expects($this->exactly(2))
            ->method("updateProgress");

        $storage = $this->getStorageMock();
        $storage->expects($this->exactly(3))
            ->method("isCompleted");
        $storage->expects($this->once())
            ->method("markAsComplete");

        $processor = new Processor(
            $this->getHandlerMap([
                $handler
            ]),
            $dispatcher,
            $storage
        );

        $dispatcher->expects($this->exactly(2))
            ->method("dispatch")
            ->withConsecutive(
                [$this->anything(), $this->isInstanceOf(AchievementProgressedEvent::class)],
                [$this->anything(), $this->isInstanceOf(AchievementCompletedEvent::class)]
            );

        $event = new ProgressUpdateEvent("test-1", 1, []);
        $processor->processEvent($event);
        $event = new ProgressUpdateEvent("test-1", 1, []);
        $processor->processEvent($event);
        //this should be ignored as the achievement was already obtained last time
        $event = new ProgressUpdateEvent("test-1", 1, []);
        $processor->processEvent($event);
    }

    protected function getEventDispatcherMock()
    {
        $mock = $this->createMock(EventDispatcherInterface::class);

        return $mock;
    }

    protected function getStorageMock()
    {
        $mock = $this->createMock(CompletionStorage::class);

        $mock->method("markAsComplete")->willReturnCallback(function ($achievementId, $userId) {
            if (empty(self::$storageData)) {
                self::$storageData = [];
            }

            self::$storageData[] = $achievementId . "_" . $userId;
            return true;
        });

        $mock->method("isCompleted")->willReturnCallback(function ($achievementId, $userId) {
            return is_array(self::$storageData) && in_array($achievementId . "_" . $userId, self::$storageData);
        });

        return $mock;
    }

    protected function getHandlerMap(array $handlers = [])
    {
        $handlerMap = new HandlerMap();
        foreach ($handlers as $handler) {
            $handlerMap->registerHandler($handler);
        }

        return $handlerMap;
    }

    public function testNoHandlerException()
    {
        $processor = new Processor(
            $this->getHandlerMap(),
            $this->getEventDispatcherMock(),
            $this->getStorageMock()
        );

        $this->expectException(HandlerNotFoundException::class);

        $event = new ProgressUpdateEvent("inexistent-1", 1, []);

        $processor->processEvent($event);
    }

    protected function setUp()
    {
        self::$storageData = null;
    }
}
