<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\RequestFormatNegotiator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestFormatNegotiationListener implements EventSubscriberInterface
{
    private ?array $formats;
    private ?array $priorities;

    public function __construct(
        array $formats = null,
        array $priorities = null,
    ) {
        $this->formats = $formats;
        $this->priorities = $priorities;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        RequestFormatNegotiator::negotiate(
            $event->getRequest(),
            $this->formats,
            $this->priorities,
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
