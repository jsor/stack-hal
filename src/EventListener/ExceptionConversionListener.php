<?php

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\ExceptionConverter;
use Jsor\Stack\Hal\Response\VndErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionConversionListener implements EventSubscriberInterface
{
    private $prettyPrint;
    private $debug;
    private $logger;

    public function __construct(LoggerInterface $logger = null,
                                $prettyPrint = false,
                                $debug = false)
    {
        $this->logger = $logger;
        $this->prettyPrint = (bool) $prettyPrint;
        $this->debug = (bool) $debug;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (null !== $this->logger) {
            ExceptionConverter::logException($this->logger, $event->getException());
        }

        $event->setResponse(VndErrorResponse::fromException($event->getException(), $this->prettyPrint, $this->debug));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException',
        );
    }
}
