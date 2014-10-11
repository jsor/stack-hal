<?php

namespace Jsor\Stack\Hal\Exception;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ValidatorBuilder;

/**
 * @group no-deps
 */
class ValidationErrorExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_serializes_exception_to_json()
    {
        $constraint = new Collection(array(
            'email' => new Email(),
            'name' => new NotBlank(),
            'birthday' => new Date(),
        ));

        $data = array(
            'additional' => 'foo'
        );

        $builder = new ValidatorBuilder();

        $violationList = $builder->getValidator()->validateValue($data, $constraint);

        $exception = new ValidationErrorException($violationList, 'Validation failed', 100);

        $this->assertSame(400, $exception->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'message' => 'Validation failed',
                    'logref' => 100,
                    '_embedded' =>
                        [
                            'errors' =>
                                [
                                    [
                                        'message' => 'This field is missing.',
                                        'path' => '/email',
                                    ],
                                    [
                                        'message' => 'This field is missing.',
                                        'path' => '/name',
                                    ],
                                    [
                                        'message' => 'This field is missing.',
                                        'path' => '/birthday',
                                    ],
                                    [
                                        'message' => 'This field was not expected.',
                                        'path' => '/additional',
                                    ],
                                ],
                        ],
                ]
            ),
            $exception->getHal()->asJson()
        );
    }
}
