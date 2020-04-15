<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class NameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ],
            ])
            ->add('last_name', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 4]),
                ],
            ]);
    }
}
