<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Pseudo'
            ))
			->add('password', PasswordType::class, array(
                'constraints' => new Assert\NotBlank(), 'attr' => array('class' => 'redactor'), 'label' => 'Texte'
            ))
			->add('login', SubmitType::class, array(
                'label' => 'Connectez-vous',
				'attr' => array('class' => 'btn btn-info')
            ));
    }

    public function getName()
    {
        return 'login';
    }
}