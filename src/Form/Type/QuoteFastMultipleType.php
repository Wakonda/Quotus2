<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\Quote;
use App\Entity\Language;
use App\Repository\LanguageRepository;

class QuoteFastMultipleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$locale = $options["locale"];

        $builder
			->add('url', TextType::class, array(
                'constraints' => [new Assert\NotBlank(), new Assert\Url()], 'label' => 'URL', 'mapped' => false
            ))
			->add('ipProxy', TextType::class, array(
                'label' => 'admin.quote.ProxyAddress', 'required' => false, 'mapped' => false, 'constraints' => [new Assert\Regex("#^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}:[0-9]{2,4}$#")]
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
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')));
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"data_class" => Quote::class,
			"locale" => null
		));
	}

    public function getName()
    {
        return 'quotefastmultiple';
    }
}