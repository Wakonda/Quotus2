<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\Page;
use App\Entity\Language;
use App\Repository\LanguageRepository;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$locale = $options["locale"];

        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.page.Title"
            ))
			->add('text', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.page.Text", 'attr' => array('class' => 'redactor')
            ))
			->add('internationalName', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.page.InternationalName"
            ))
			->add('photo', FileSelectorType::class, array("label" => "admin.page.Image", "required" => true, "current_file" => $builder->getData()->getPhoto(), "path_file" => Page::PATH_FILE))
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
			"data_class" => Page::class,
			"locale" => null
		));
	}

    public function getName()
    {
        return 'page';
    }
}