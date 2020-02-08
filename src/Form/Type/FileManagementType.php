<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use App\Repository\LanguageRepository;

use App\Entity\Language;

class FileManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, ["label" => "fileManagement.form.Description", 'attr' => array('class' => 'redactor'), 'constraints' => new Assert\NotBlank()])
            ->add('folder', HiddenType::class)
			->add('photo', FileSelectorType::class, array("label" => "fileManagement.form.Photo", "required" => true, "mapped" => false))
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'current_file' => null,
            'path_file' => null
        ]);
	}
}