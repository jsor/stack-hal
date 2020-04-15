<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\Response\HalResponse;
use Nocarrier\Hal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ResponseConversionListener implements EventSubscriberInterface
{
    private $prettyPrint;

    public function __construct($prettyPrint = true)
    {
        $this->prettyPrint = (bool) $prettyPrint;
    }

    public function onKernelView(ViewEvent $event): void
    {
        $hal = $event->getControllerResult();

        if ($hal instanceof Hal) {
            $event->setResponse(
                new HalResponse($hal, 200, [], $this->prettyPrint)
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => 'onKernelView',
        ];
    }
}
