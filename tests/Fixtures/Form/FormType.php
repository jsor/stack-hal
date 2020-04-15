<?php

declare(strict_types=1);

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

final class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('person', PersonType::class)
            ->add('family', CollectionType::class, [
                'entry_type' => PersonType::class,
                'allow_add' => true,
            ])
            ->add('friends', CollectionType::class, [
                'entry_type' => PersonType::class,
                'allow_add' => true,
                'constraints' => [
                    new Count(['min' => 1]),
                ],
            ])
            ->add('newsletter', CheckboxType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }
}
