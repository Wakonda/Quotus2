<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use App\Repository\LanguageRepository;
use App\Entity\Language;
use App\Entity\Store;

class StoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$locale = $options["locale"];

        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.store.Title"
            ))
            ->add('biography', BiographySelectorType::class, array(
                'label' => 'admin.store.Biography'
            ))
			->add('newBiography', HiddenType::class, array("mapped" => false))
            ->add('text', TextareaType::class, array( 'attr' => array('class' => 'redactor'), 
                'constraints' => new Assert\NotBlank(), "label" => "admin.store.Text"
            ))
			->add('photo', FileType::class, array('data_class' => null, "label" => "admin.store.Image", "required" => true
            ))
			->add('amazonCode', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.store.ProductCode", 'attr' => array('class' => 'redactor')
            ))
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
			"data_class" => Store::class,
			"locale" => null
		));
	}

    public function getName()
    {
        return 'store';
    }
}