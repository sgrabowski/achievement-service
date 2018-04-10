<?php

namespace App\AchievementBundle\Tests\Service;

use App\AchievementBundle\Event\AchievementCompletedEvent;
use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Handler\HandlerInterface;
use App\AchievementBundle\Service\HandlerMap;
use App\AchievementBundle\Service\Processor;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcessorTest extends TestCase
{

    public function testCompletedAchievement()
    {
        $dispatcher = $this->getEventDispatcherMock();

        $processor = new Processor(
            $this->getHandlerMap([
                $this->getAchievementHandlerMock()
            ]),
            $dispatcher
        );

        $dispatcher->expects($this->once())
            ->method("dispatch")
            ->with($this->isInstanceOf(AchievementCompletedEvent::class));

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

    protected function getAchievementHandlerMock(bool $achieved = true)
    {
        $mock = $this->createMock(HandlerInterface::class);
        $mock->method("getAchievementId")->willReturn("test-1");
        $mock->method("isAchieved")->willReturn($achieved);
        $mock->method("getValidationConstraint")->willReturn(null);

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
