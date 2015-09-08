<?php

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\Response\HalResponse;
use Nocarrier\Hal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseConversionListener implements EventSubscriberInterface
{
    private $prettyPrint;

    public function __construct($prettyPrint = true)
    {
        $this->prettyPrint = (bool) $prettyPrint;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $hal = $event->getControllerResult();

        if ($hal instanceof Hal) {
            $event->setResponse(
                new HalResponse($hal, 200, [], $this->prettyPrint)
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'onKernelView'
        ];
    }
}
