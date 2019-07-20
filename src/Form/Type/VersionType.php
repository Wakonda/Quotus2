<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$locale = $options["locale"];
		
        $builder
            ->add('versionNumber', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Numéro de version"
            ))
			->add('releaseDate', DateType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'widget' => 'single_text'
            ))
			->add('file', FileType::class, array('data_class' => null, "label" => "Fichier", "required" => true))
            ->add('save', SubmitType::class, array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')))
			;
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"locale" => null
		));
	}
	
    public function getName()
    {
        return 'version';
    }
}