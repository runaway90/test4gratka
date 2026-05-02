<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Twig\Environment;

class ExceptionListener
{
    public function __construct(
        private LoggerInterface $logger,
        private Environment $twig
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AuthenticationException || $exception instanceof AccessDeniedException) {
            return;
        }

        $this->logger->error($exception->getMessage(), ['exception' => $exception]);

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $request = $event->getRequest();

        if ($request->isXmlHttpRequest() || str_contains($request->headers->get('Accept', ''), 'application/json')) {
            $detail = $exception instanceof HttpExceptionInterface
                ? $exception->getMessage()
                : 'An unexpected error occurred';

            $event->setResponse(new JsonResponse(
                ['detail' => $detail],
                $statusCode,
                ['Content-Type' => 'application/problem+json']
            ));

            return;
        }

        $html = $this->twig->render('bundles/TwigBundle/Exception/error.html.twig', [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Error',
        ]);

        $event->setResponse(new Response($html, $statusCode));
    }
}
