<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('password', RepeatedType::class, array(
				'label' => 'editPassword.field.NewPassword',
				'type' => PasswordType::class,
				'options' => array('required' => true),
				'first_options'  => array('label' => 'editPassword.field.NewPassword'),
				'second_options' => array('label' => 'editPassword.field.PasswordValidation'),
				'constraints' => new Assert\NotBlank()
			))
			
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'updatepassword';
    }
}
