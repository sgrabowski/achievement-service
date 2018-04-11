<?php

namespace App\AchievementBundle\Tests\Handler;

use App\AchievementBundle\Event\ProgressUpdateEvent;
use App\AchievementBundle\Exception\PayloadValidationException;
use App\AchievementBundle\Handler\PayloadValidatingHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PayloadValidatingHandlerTest extends TestCase
{

    public function testPayloadValidationException()
    {
        $handler = $this->getMockBuilder(PayloadValidatingHandler::class)
            ->setConstructorArgs([
                $this->getValidatorMock(new ConstraintViolationList([
                    new ConstraintViolation("fug :D", "ayyy lmao", [], null, "some.path", "gooby pls")
                ]))
            ])
            ->getMock();

        $this->expectException(PayloadValidationException::class);

        $event = new ProgressUpdateEvent("instant-1", 1, []);

        $handler->validatePayload($event);
    }

    public function testPayloadValid()
    {
        $handler = $this->getMockBuilder(PayloadValidatingHandler::class)
            ->setConstructorArgs([
                $this->getValidatorMock(new ConstraintViolationList())
            ])
            ->getMock();

        $event = new ProgressUpdateEvent("instant-1", 1, []);

        $handler->validatePayload($event);
    }

    protected function getValidatorMock(ConstraintViolationListInterface $validationErrors = null)
    {
        $mock = $this->createMock(ValidatorInterface::class);
        $mock->method("validate")
            ->willReturn($validationErrors ?? new ConstraintViolationList());
        $mock->expects($this->once())
            ->method("validate");
        return $mock;
    }
}