<?php

namespace TB\Bundle\FrontendBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use TB\Bundle\FrontendBundle\Entity\User;
use TB\Bundle\FrontendBundle\Form\DataTransformer\GeometryPointTransformer;

/**
 * Extend the ProfileFormType from of the FOSUserBundle to add custom fields and functionallity
 */
class ProfileFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildUserForm($builder, $options);
        $transformer = new GeometryPointTransformer();

        // add your custom fields not defined in FOSUserBundle
        $builder->add('firstName');
        $builder->add('lastName');
        $builder->add(
            $builder
                ->create('location', 'text', ['label' => 'Where do you call home?'])
                ->addModelTransformer($transformer)
        );
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
        
        $builder->remove('email');
        $builder->remove('username');
    }

    public function getName()
    {
        return 'tb_user_profile';
    }
}