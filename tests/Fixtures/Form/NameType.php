<?php

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ],
            ])
            ->add('last_name', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 4]),
                ],
            ]);
    }

    public function getName()
    {
        return 'name';
    }
}
