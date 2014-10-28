<?php

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\Response\VndErrorResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionConversionListener implements EventSubscriberInterface
{
    private $prettyPrint;
    private $debug;

    public function __construct($prettyPrint = false,
                                $debug = false)
    {
        $this->prettyPrint = (bool) $prettyPrint;
        $this->debug = (bool) $debug;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $event->setResponse(VndErrorResponse::fromException($event->getException(), $this->prettyPrint, $this->debug));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -254),
        );
    }
}
