<?php

namespace Jsor\Stack\Hal\ResponseConverter;

use Jsor\Stack\Hal\ResponseConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EventSubscriber implements EventSubscriberInterface
{
    private $prettyPrint;

    public function __construct($prettyPrint = false)
    {
        $this->prettyPrint = (bool) $prettyPrint;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $response = ResponseConverter::convert(
            $event->getControllerResult(),
            $event->getRequest(),
            $this->prettyPrint
        );

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => array('onKernelView', -10),
        );
    }
}
