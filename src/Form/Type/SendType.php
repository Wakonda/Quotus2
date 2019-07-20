<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('firstName', TextType::class, array('required' => false, "label" => "send.field.FirstName"))
			->add('lastName', TextType::class, array('required' => false, "label" => "send.field.LastName"))
            ->add('yourMail', TextType::class, array('constraints' => array(new Assert\Email(), new Assert\NotBlank()), "label" => "send.field.YourMail"))
            ->add('recipientMail', TextType::class, array('constraints' => array(new Assert\Email(), new Assert\NotBlank()), "label" => "send.field.RecipientMail"))
            ->add('subject', TextType::class, array('constraints' => new Assert\NotBlank(), "label" => "send.field.Subject"))
			->add('message', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "send.field.Message", 'attr' => array('class' => TextType::class)
            ))
			->add('send', SubmitType::class, array('label' => 'send.field.Send', 'attr' => array('class' => 'btn btn-success')))
			;
    }

    public function getName()
    {
        return 'send';
    }
}