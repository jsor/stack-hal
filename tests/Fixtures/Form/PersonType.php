<?php

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', 'choice', [
                'choices' => ['m' => 'Male', 'f' => 'Female'],
                'mapped' => false,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('name', new NameType(), [
                'mapped' => false,
                'constraints' => [
                    new Valid()
                ]
            ])
            ->add('emails', 'collection', [
                'type'      => 'email',
                'allow_add' => true,
                'options'  => [
                    'constraints' => [
                        new Email()
                    ],
                ],
            ]);
    }

    public function getName()
    {
        return 'person';
    }
}
