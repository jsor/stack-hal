<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\ExceptionConverter;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ExceptionConversionListener implements EventSubscriberInterface
{
    private $logger;
    private $prettyPrint;
    private $debug;
    private $formats;

    public function __construct(
        LoggerInterface $logger = null,
        bool $prettyPrint = true,
        bool $debug = false,
        array $formats = null
    ) {
        $this->logger = $logger;
        $this->prettyPrint = $prettyPrint;
        $this->debug = $debug;
        $this->formats = $formats;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $response = ExceptionConverter::handleThrowable(
            $event->getThrowable(),
            $event->getRequest(),
            $this->logger,
            $this->prettyPrint,
            $this->debug,
            $this->formats
        );

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
