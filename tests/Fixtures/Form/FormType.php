<?php

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', new PersonType())
            ->add('family', 'collection', [
                'type'      => new PersonType(),
                'allow_add' => true
            ])
            ->add('friends', 'collection', [
                'type'        => new PersonType(),
                'allow_add'   => true,
                'constraints' => [
                    new Count(['min' => 1]),
                ]
            ])
            ->add('newsletter', 'checkbox', [
                'constraints' => [
                    new NotBlank()
                ],
            ]);
    }

    public function getName()
    {
        return 'form';
    }
}
