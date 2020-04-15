<?php

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', 'Jsor\Stack\Hal\Fixtures\Form\PersonType')
            ->add('family', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'entry_type' => 'Jsor\Stack\Hal\Fixtures\Form\PersonType',
                'allow_add' => true,
            ])
            ->add('friends', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'entry_type' => 'Jsor\Stack\Hal\Fixtures\Form\PersonType',
                'allow_add' => true,
                'constraints' => [
                    new Count(['min' => 1]),
                ],
            ])
            ->add('newsletter', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    public function getName()
    {
        return 'form';
    }
}
