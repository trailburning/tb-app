<?php

namespace TB\Bundle\FrontendBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;


/**
 * Extend the registration form from of the FOSUserBundle to add custom fields and functionallity
 */
class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom fields not defined in FOSUserBundle
        $builder->add('firstName');
        $builder->add('lastName');
        $builder->add('location');
        
        $builder->remove('username');
    }

    public function getName()
    {
        return 'tb_user_registration';
    }
}