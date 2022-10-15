<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Exception;

use Jsor\Stack\Hal\Fixtures\Form\FormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

final class FormErrorExceptionTest extends TestCase
{
    private const DATA = [
        'family' => [
            [
                'name' => [
                    'first_name' => 'Jan',
                ],
            ],
        ],
        'additional' => 'foo',
    ];

    /** @test */
    public function it_serializes_exception_to_json(): void
    {
        $validator = Validation::createValidator();
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();

        $form = $formFactory->create(FormType::class);

        $form->submit(self::DATA);

        $exception = new FormErrorException($form, 'Invalid form', 100);

        $this->assertSame(400, $exception->getStatusCode());
        $this->assertEqualsCanonicalizing(
            [
                'message' => 'Invalid form',
                'logref' => 100,
                '_embedded' => [
                        'errors' => [
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
            ],
            json_decode($exception->getHal()->asJson(), true),
        );
    }
}
