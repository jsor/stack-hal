<?php

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', 'choice', array(
                'choices' => array('m' => 'Male', 'f' => 'Female'),
                'mapped' => false,
                'constraints' => array(
                    new NotBlank()
                ),
            ))
            ->add('name', new NameType(), array(
                'mapped' => false,
                'constraints' => array(
                    new Valid()
                )
            ));
    }

    public function getName()
    {
        return 'person';
    }
}
