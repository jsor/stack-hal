<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Exception;

use Nocarrier\Hal;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

final class FormErrorException extends BadRequestHttpException implements HalException
{
    private FormInterface $form;
    private mixed $logref;

    public function __construct(
        FormInterface $form,
        string $message = '',
        mixed $logref = null,
        Throwable $previous = null,
        int $code = 0,
    ) {
        parent::__construct($message, $previous, $code);

        $this->form = $form;
        $this->logref = $logref;
    }

    public function getHal(): Hal
    {
        $data = [
            'message' => $this->getMessage(),
        ];

        if ($this->logref) {
            $data['@logref'] = $this->logref;
        }

        $hal = new Hal(null, $data);

        $this->appendErrors($hal, $this->form);

        return $hal;
    }

    private function appendErrors(Hal $hal, FormInterface $form, array &$visited = []): void
    {
        $formPath = null;

        /* @var $error FormError */
        foreach ($form->getErrors() as $error) {
            $data = [
                'message' => $error->getMessage(),
            ];

            $origin = $error->getOrigin();

            if (null !== $origin) {
                $currPath = $this->getPath($origin);
            } else {
                if (null === $formPath) {
                    $formPath = $this->getPath($form);
                }

                $currPath = $formPath;
            }

            if ($currPath) {
                if (isset($visited[$currPath])) {
                    continue;
                }

                $visited[$currPath] = true;
                $data['path'] = $currPath;
            }

            $hal->addResource('errors', new Hal(null, $data));
        }

        foreach ($form->all() as $child) {
            $this->appendErrors($hal, $child, $visited);
        }
    }

    private function getPath(FormInterface $form): string
    {
        $path = [];

        $parent = $form;

        while ($parent) {
            array_unshift($path, $parent->getName());
            $parent = $parent->getParent();
        }

        // Remove root form
        array_shift($path);

        return '/' . implode('/', $path);
    }
}
