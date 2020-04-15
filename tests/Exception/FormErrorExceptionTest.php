<?php

namespace Jsor\Stack\Hal\Exception;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

class FormErrorExceptionTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function it_serializes_exception_to_json()
    {
        $validator = Validation::createValidator();
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();

        $form = $formFactory->create('Jsor\Stack\Hal\Fixtures\Form\FormType');

        $data = [
            'family' => [
                [
                    'name' => [
                        'first_name' => 'Jan',
                    ]
                ]
            ],
            'additional' => 'foo'
        ];

        $form->submit($data);

        $exception = new FormErrorException($form, 'Invalid form', 100);

        $this->assertSame(400, $exception->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            \json_encode(
                [
                    'message' => 'Invalid form',
                    'logref' => 100,
                    '_embedded' =>
                        [
                            'errors' =>
                                [
                                    [
                                        'message' => 'This form should not contain extra fields.',
                                        'path' => '/',
                                    ],
                                    [
                                        'message' => 'This collection should contain 1 element or more.',
                                        'path' => '/friends',
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
                                        'path' => '/family/0/gender',
                                    ],
                                    [
                                        'message' => 'This value should not be blank.',
                                        'path' => '/family/0/name/last_name',
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
