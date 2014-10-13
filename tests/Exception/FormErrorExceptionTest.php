<?php

namespace Jsor\Stack\Hal\Exception;

use Jsor\Stack\Hal\Fixtures\Form\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

class FormErrorExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_serializes_exception_to_json()
    {
        $validator = Validation::createValidator();
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();

        $form = $formFactory->create(new FormType());

        $data = array(
            'additional' => 'foo'
        );

        $form->submit($data);

        $exception = new FormErrorException($form, 'Invalid form', 100);

        $this->assertSame(400, $exception->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Invalid form',
                    'logref' => 100,
                    '_embedded' =>
                        [
                            'errors' =>
                                [
                                    [
                                        'message' => 'This form should not contain extra fields.',
                                    ],
                                    [
                                        'message' => 'This value should not be blank.',
                                        'path' => '/person/gender',
                                    ],
                                    [
                                        'message' => 'This value should not be blank.',
                                        'path' => '/person/name/first_name',
                                    ],
                                    [
                                        'message' => 'This value should not be blank.',
                                        'path' => '/person/name/last_name',
                                    ],
                                    [
                                        'message' => 'This value should not be blank.',
                                        'path' => '/newsletter',
                                    ],
                                ],
                        ],
                ]
            ),
            $exception->getHal()->asJson()
        );
    }
}
