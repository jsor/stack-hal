<?php

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\RequestFormatNegotiator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestFormatNegotiationListener implements EventSubscriberInterface
{
    private $formats;
    private $priorities;

    public function __construct(
        array $formats = null,
        array $priorities = null
    ) {
        $this->formats = $formats;
        $this->priorities = $priorities;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        RequestFormatNegotiator::negotiate(
            $event->getRequest(),
            $this->formats,
            $this->priorities
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
