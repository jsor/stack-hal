<?php

namespace Jsor\Stack\Hal\Exception;

use Nocarrier\Hal;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FormErrorException extends BadRequestHttpException implements HalException
{
    private $form;
    private $logref;

    public function __construct(FormInterface $form,
                                $message = null,
                                $logref = null,
                                \Exception $previous = null,
                                $code = 0)
    {
        parent::__construct($message, $previous, $code);

        $this->form = $form;
        $this->logref = $logref;
    }

    public function getHal()
    {
        $data = [
            'message' => $this->getMessage()
        ];

        if ($this->logref) {
            $data['@logref'] = $this->logref;
        }

        $hal = new Hal(null, $data);

        $this->appendErrors($hal, $this->form);

        return $hal;
    }

    private function appendErrors(Hal $hal, FormInterface $form)
    {
        $formPath = null;

        /* @var $error FormError */
        foreach ($form->getErrors() as $error) {
            $data = [
                'message' => $error->getMessage()
            ];

            $origin = $error->getOrigin();

            if ($origin) {
                $currPath = $this->getPath($origin);
            } else {
                if (null === $formPath) {
                    $formPath = $this->getPath($form);
                }

                $currPath = $formPath;
            }

            if ($currPath) {
                $data['path'] = $currPath;
            }

            $hal->addResource('errors', new Hal(null, $data));
        }

        foreach ($form->all() as $child) {
            $this->appendErrors($hal, $child);
        }
    }

    private function getPath(FormInterface $form)
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
