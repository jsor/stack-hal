<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\RequestFormatValidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestFormatValidationListener implements EventSubscriberInterface
{
    private $acceptableFormats;
    private $exclude;

    public function __construct(
        array $acceptableFormats = null,
        $exclude = null
    ) {
        $this->acceptableFormats = $acceptableFormats;
        $this->exclude = $exclude;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $response = RequestFormatValidator::intercept(
            $event->getRequest(),
            $this->acceptableFormats,
            $this->exclude
        );

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
