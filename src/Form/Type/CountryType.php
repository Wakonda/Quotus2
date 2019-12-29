<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\Country;
use App\Entity\Language;
use App\Repository\LanguageRepository;

class CountryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$locale = $options["locale"];
		
        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.country.Title"
            ))
			->add('internationalName', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.country.InternationalName", 'attr' => array('class' => 'redactor')
            ))
			->add('flag', FileSelectorType::class, array("label" => "admin.country.Flag", "required" => true, "current_file" => $builder->getData()->getFlag(), "path_file" => Country::PATH_FILE))
			->add('language', EntityType::class, array(
				'label' => 'admin.form.Language', 
				'class' => Language::class,
				'query_builder' => function (LanguageRepository $er) use ($locale) {
					return $er->findAllForChoice($locale);
				},
				'multiple' => false,
				'required' => true,
				'expanded' => false,
				'placeholder' => 'main.field.ChooseAnOption',
				'constraints' => new Assert\NotBlank()
			))
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')))
			;
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"data_class" => Country::class,
			"locale" => null
		));
	}

    public function getName()
    {
        return 'country';
    }
}