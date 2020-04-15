<?php

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => ['m' => 'Male', 'f' => 'Female'],
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('name', 'Jsor\Stack\Hal\Fixtures\Form\NameType', [
                'mapped' => false,
            ]);
    }

    public function getName()
    {
        return 'person';
    }
}
