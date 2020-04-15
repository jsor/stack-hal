<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => ['m' => 'Male', 'f' => 'Female'],
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('name', NameType::class, [
                'mapped' => false,
            ]);
    }
}
