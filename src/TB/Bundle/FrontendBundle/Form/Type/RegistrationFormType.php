<?php

namespace TB\Bundle\FrontendBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use TB\Bundle\FrontendBundle\Entity\User;

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
        $builder->add('location', 'text', ['label' => 'Where do you call home?']);
        $builder->add('about', 'textarea', ['label' => 'Tell us a little bit about yourself']);
        $builder->add('gender', 'choice', [
            'label' => 'What gender are you?',
            'choices' => [
                User::GENDER_NONE => 'I\'d rather not say',
                User::GENDER_MALE => 'Male',
                User::GENDER_FEMALE => 'Female',
            ],
        ]);
        
        $builder->add('newsletter', 'checkbox', ['label' => 'Receive Trailburning newsletter', 'required' => false]);
        
        $builder->remove('username');
    }

    public function getName()
    {
        return 'tb_user_registration';
    }
}