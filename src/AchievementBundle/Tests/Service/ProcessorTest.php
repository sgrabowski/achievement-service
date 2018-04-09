<?php

namespace App\AchievementBundle\Tests\Service;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\HandlerNotFoundException;
use App\AchievementBundle\Handler\Handler;
use App\AchievementBundle\Handler\PersistingHandler;
use App\AchievementBundle\Service\HandlerMap;
use App\AchievementBundle\Service\Manager;
use App\AchievementBundle\Service\Processor;
use App\AchievementBundle\Service\ProgressStorage;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProcessorTest extends TestCase
{

    protected function getValidatorMock(ConstraintViolationListInterface $validationErrors = null)
    {
        $mock = $this->createMock(ValidatorInterface::class);
        $mock->method("validate")
            ->willReturn($validationErrors ?? new ConstraintViolationList());
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

    protected function getInstantAchievementHandlerMock()
    {
        $mock = $this->createMock(Handler::class);
        $mock->method("getAchievementId")->willReturn("instant-1");
        $mock->method("isAchieved")->willReturn(true);
        $mock->method("getValidationConstraint")->willReturn(null);

        return $mock;
    }

    protected function getPersistingAchievementHandlerMock()
    {
        //counter needs to hit 50 for the achievement to be completed
        $counter = 0;
        $mock = $this->createMock(PersistingHandler::class);
        $mock->method("getAchievementId")->willReturn("persisting-1");
        $mock->method("isAchieved")->willReturnCallback(function () use (&$counter) {
            return $counter >= 50;
        });
        $mock->method("getValidationConstraint")->willReturn(null);
        $mock->method("calculateProgress")->willReturnCallback(function () use (&$counter) {
            return (float)($counter / 50)*100;
        });
        $mock->method("process")->willReturnCallback(function (ProgressUpdateEvent $e) use (&$counter) {
            $payload = $e->getPayload();
            $counter += $payload['incrementation'];
        });
        $mock->method("getProgressData")->willReturnCallback(function () use (&$counter) {
            return ['counter' => $counter];
        });

        return $mock;
    }

    protected function getProgressStorageMock()
    {
        $mock = $this->createMock(ProgressStorage::class);

        return $mock;
    }

    protected function getManagerMock()
    {
        $mock = $this->createMock(Manager::class);

        return $mock;
    }

    public function testInstantAchievement()
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->setConstructorArgs([
                $this->getValidatorMock(),
                $this->getHandlerMap([
                    $this->getInstantAchievementHandlerMock()
                ]),
                $this->getProgressStorageMock(),
                $this->getManagerMock()
            ])
            ->setMethods(['achieve'])
            ->getMock();

        $processor->expects($this->once())
            ->method("achieve");

        $event = new ProgressUpdateEvent("instant-1", 1, []);

        $processor->processEvent($event);
    }

    public function testNoHandlerException()
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->setConstructorArgs([
                $this->getValidatorMock(),
                $this->getHandlerMap(),
                $this->getProgressStorageMock(),
                $this->getManagerMock()
            ])
            ->setMethods(['achieve'])
            ->getMock();

        $this->expectException(HandlerNotFoundException::class);

        $event = new ProgressUpdateEvent("instant-1", 1, []);

        $processor->processEvent($event);
    }

    public function testPayloadValidationException()
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->setConstructorArgs([
                $this->getValidatorMock(new ConstraintViolationList([
                    new ConstraintViolation("fug :D", "ayyy lmao", [], null, "some.path", "gooby pls")
                ])),
                $this->getHandlerMap(),
                $this->getProgressStorageMock(),
                $this->getManagerMock()
            ])
            ->setMethods(['achieve'])
            ->getMock();

        $this->expectException(HandlerNotFoundException::class);

        $event = new ProgressUpdateEvent("instant-1", 1, []);

        $processor->processEvent($event);
    }

    public function testPersistingAchievement()
    {
        $handler = $this->getPersistingAchievementHandlerMock();
        $progressStorage = $this->getProgressStorageMock();
        $manager = $this->getManagerMock();

        $processor = $this->getMockBuilder(Processor::class)
            ->setConstructorArgs([
                $this->getValidatorMock(),
                $this->getHandlerMap([
                    $handler
                ]),
                $progressStorage,
                $manager
            ])
            ->setMethods(['achieve'])
            ->getMock();

        $handler->expects($this->exactly(3))
            ->method("setProgressData");

        $handler->expects($this->exactly(3))
            ->method("getProgressData");

        $progressStorage->expects($this->exactly(3))
            ->method("retrieve");

        $progressStorage->expects($this->exactly(3))
            ->method("store");

        $processor->expects($this->once())
            ->method("achieve");

        $manager->expects($this->exactly(3))
            ->method("updateUserProgress")
            ->withConsecutive(
                [$this->equalTo('persisting-1'), $this->equalTo(1), $this->equalTo(2, 0.1)],
                [$this->equalTo('persisting-1'), $this->equalTo(1), $this->equalTo(22, 0.1)],
                [$this->equalTo('persisting-1'), $this->equalTo(1), $this->equalTo(100, 0.1)]
            );

        $event = new ProgressUpdateEvent("persisting-1", 1, ['incrementation' => 1]);
        $processor->processEvent($event);

        $event = new ProgressUpdateEvent("persisting-1", 1, ['incrementation' => 10]);
        $processor->processEvent($event);

        $event = new ProgressUpdateEvent("persisting-1", 1, ['incrementation' => 100]);
        $processor->processEvent($event);
    }
}
