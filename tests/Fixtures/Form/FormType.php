<?php

namespace Jsor\Stack\Hal\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', new PersonType(), array(
                'mapped' => false
            ))
            ->add('newsletter', 'checkbox', array(
                'constraints' => array(
                    new NotBlank()
                ),
            ));
    }

    public function getName()
    {
        return 'form';
    }
}