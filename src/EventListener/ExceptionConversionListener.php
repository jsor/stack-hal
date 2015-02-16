<?php

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\ExceptionConverter;
use Jsor\Stack\Hal\Response\VndErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionConversionListener implements EventSubscriberInterface
{
    private $logger;
    private $prettyPrint;
    private $debug;
    private $formats;

    public function __construct(LoggerInterface $logger = null,
                                $prettyPrint = true,
                                $debug = false,
                                array $formats = null)
    {
        $this->logger = $logger;
        $this->prettyPrint = (bool) $prettyPrint;
        $this->debug = (bool) $debug;
        $this->formats = $formats;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $response = ExceptionConverter::handleException(
            $event->getException(),
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

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException',
        );
    }
}
