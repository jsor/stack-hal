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
            ->add('first_name', 'text', array(
                'mapped' => false,
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 4)),
                ),
            ))
            ->add('last_name', 'text', array(
                'mapped' => false,
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 4)),
                ),
            ));
    }

    public function getName()
    {
        return 'name';
    }
}
