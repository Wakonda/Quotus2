<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'comment.field.Message'
            ))		
            ->add('save', SubmitType::class, array('label' => 'comment.field.Send', 'attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'comment';
    }
}