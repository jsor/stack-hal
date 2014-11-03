<?php

namespace Jsor\Stack\Hal\EventListener;

use Jsor\Stack\Hal\RequestFormatValidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestFormatValidationListener implements EventSubscriberInterface
{
    private $acceptableFormats;

    public function __construct(array $acceptableFormats = null)
    {
        $this->acceptableFormats = $acceptableFormats;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $response = RequestFormatValidator::intercept(
            $event->getRequest(),
            $this->acceptableFormats
        );

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest'
        );
    }
}
