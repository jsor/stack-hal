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

        $this->appendErrors($hal, $this->form, '');

        return $hal;
    }

    private function appendErrors(Hal $hal, FormInterface $form, $path)
    {
        $path = rtrim($path, '/');

        /* @var $error FormError */
        foreach ($form->getErrors() as $error) {
            $data = [
                'message' => $error->getMessage()
            ];

            $currPath = $path;
            $origin   = $error->getOrigin();

            if ($origin) {
                $currPath .= '/' . $origin->getConfig()->getName();
            }

            if ($currPath) {
                $data['path'] = $currPath;
            }

            $hal->addResource('errors', new Hal(null, $data));
        }

        foreach ($form->all() as $child) {
            $this->appendErrors($hal, $child, $path . '/' . $child->getName());
        }
    }
}
