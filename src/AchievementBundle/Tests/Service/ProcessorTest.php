<?php

namespace App\AchievementBundle\Tests\Service;

use App\AchievementBundle\Event\AchievementCompletedEvent;
use App\AchievementBundle\Event\AchievementProgressedEvent;
use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Handler\HandlerInterface;
use App\AchievementBundle\Service\HandlerMap;
use App\AchievementBundle\Service\Processor;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcessorTest extends TestCase
{

    public function testAchievementProgressionAndCompletion()
    {
        $dispatcher = $this->getEventDispatcherMock();

        $processor = new Processor(
            $this->getHandlerMap([
                $this->getAchievementHandlerMock()
            ]),
            $dispatcher
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
    }

    protected function getEventDispatcherMock()
    {
        $mock = $this->createMock(EventDispatcherInterface::class);

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

    protected function getAchievementHandlerMock($achievementId = "test-1", $tags = ['test-1', 'test2'])
    {
        $mock = $this->createMock(HandlerInterface::class);
        $mock->method("getAchievementId")->willReturn($achievementId);
        $mock->method("getValidationConstraint")->willReturn(null);
        $mock->method("getTriggeredByTags")->willReturn($tags);
        $mock->method("updateProgress")->willReturnOnConsecutiveCalls(false, true);

        return $mock;
    }

    public function testNoHandlerException()
    {
        $processor = new Processor(
            $this->getHandlerMap(),
            $this->getEventDispatcherMock()
        );

        $this->expectException(HandlerNotFoundException::class);

        $event = new ProgressUpdateEvent("inexistent-1", 1, []);

        $processor->processEvent($event);
    }
}
