<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\CountryRepository;

use App\Entity\Country;

class IndexSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$locale = $options["locale"];

        $builder
			->add('text', TextType::class, array("label" => "main.field.Keywords", "required" => false, "attr" => array("class" => "tagit full_width")))
			->add('country', EntityType::class, array(
				'label' => 'main.field.Country',
				'class' => Country::class,
				'query_builder' => function (CountryRepository $er) use ($locale) {
					return $er->findAllForChoice($locale);
				},
				'multiple' => false, 
				'expanded' => false,
				'required' => false,
				'constraints' => array(new Assert\NotBlank()),
				'placeholder' => 'main.field.ChooseAnOption'
			))
            ->add('search', SubmitType::class, array('label' => 'main.field.Search', "attr" => array("class" => "btn btn-primary")))
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
        return 'index_search';
    }
}