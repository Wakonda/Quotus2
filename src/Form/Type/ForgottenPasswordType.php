<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ForgottenPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('emailUsername', TextType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'forgottenPassword.field.EmailUsername'
            ))
			->add('captcha', TextType::class, array('label' => 'forgottenPassword.field.Captcha', "mapped" => false, "attr" => array("class" => "captcha_word"), 'constraints' => new Assert\NotBlank()))
            ->add('save', SubmitType::class, array('label' => 'forgottenPassword.field.Send', "attr" => array("class" => "btn btn-success")));
    }

    public function getName()
    {
        return 'forgottenpassword';
    }
}