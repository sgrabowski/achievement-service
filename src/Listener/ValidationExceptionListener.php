<?php

namespace App\Listener;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ValidationExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!($exception instanceof ValidationException)) {
            return;
        }

        $code = 400;

        $responseData = [
            "message" => "Validation failed",
            'validationErrors' => $exception->getValidationErrors()
        ];

        $event->setResponse(new JsonResponse($responseData, $code));
    }
}