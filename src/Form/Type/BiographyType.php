<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\CountryRepository;
use App\Repository\LanguageRepository;

use App\Entity\Country;
use App\Entity\Language;
use App\Entity\Biography;

class BiographyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$locale = $options["locale"];

        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.biography.Title"
            ))
			->add('text', TextareaType::class, array(
                'required' => false, "label" => "admin.biography.Text", 'attr' => array('class' => 'redactor')
            ))
			->add('fileManagement', FileManagementSelectorType::class, ["label" => "admin.biography.Image", "required" => false, "folder" => Biography::FOLDER])
			->add('dayBirth', IntegerType::class, array("label" => "admin.biography.BirthDate", "required" => false))
			->add('monthBirth', IntegerType::class, array("label" => "", "required" => false))
			->add('yearBirth', IntegerType::class, array("label" => "", "required" => false))
			->add('dayDeath', IntegerType::class, array("label" => "admin.biography.DeathDate", "required" => false))
			->add('monthDeath', IntegerType::class, array("label" => "", "required" => false))
			->add('yearDeath', IntegerType::class, array("label" => "", "required" => false))
			->add('country', EntityType::class, array(
				'label' => 'admin.biography.Country',
				'class' => Country::class,
				'query_builder' => function (CountryRepository $er) use ($locale) {
					return $er->findAllForChoice($locale);
				},
				'multiple' => false, 
				'expanded' => false,
				'constraints' => array(new Assert\NotBlank()),
				'placeholder' => 'main.field.ChooseAnOption'
			))
			->add('language', EntityType::class, array(
				'label' => 'admin.form.Language', 
				'class' => Language::class,
				'query_builder' => function (LanguageRepository $er) use ($locale) {
					return $er->findAllForChoice($locale);
				},
				'multiple' => false,
				'required' => false,
				'expanded' => false,
				'placeholder' => 'main.field.ChooseAnOption'
			))
			->add('type', ChoiceType::class, [
				'required' => true,
				'choices'  => [
					Biography::AUTHOR_CANONICAL => Biography::AUTHOR,
					Biography::FICTIONAL_CHARACTER_CANONICAL => Biography::FICTIONAL_CHARACTER
				],
			])
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')))
			;
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"data_class" => Biography::class,
			"locale" => null
		));
	}
	
    public function getName()
    {
        return 'biography';
    }
}