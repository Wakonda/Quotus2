<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\Source;
use App\Entity\Language;
use App\Entity\Biography;
use App\Repository\LanguageRepository;
use App\Repository\BiographyRepository;

use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class SourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$locale = $options["locale"];

        $builder
			->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'admin.source.Title'
            ))
			->add('text', TextareaType::class, array(
                'attr' => array('class' => 'redactor'), 'label' => 'admin.source.Text', "required" => false
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
		   ->add('fictionalCharacters', Select2EntityType::class, [
				'label' => 'admin.source.FictionalCharacters',
				'multiple' => true,
				'remote_route' => 'sourceadmin_getbiographiesbyajax',
				'class' => Biography::class,
				'req_params' => ['locale' => 'parent.children[language]'],
				'remote_params' => ['type' => Biography::FICTIONAL_CHARACTER],
				'page_limit' => 10,
				'primary_key' => 'id',
				'text_property' => 'title',
				'allow_clear' => true,
				'delay' => 250,
				'cache' => true,
				'cache_timeout' => 60000, // if 'cache' is true
				'language' => $locale,
				'placeholder' => 'main.field.ChooseAnOption'
			])
		   ->add('authors', Select2EntityType::class, [
				'label' => 'admin.source.Authors',
				'multiple' => true,
				'remote_route' => 'quoteadmin_getbiographiesbyajax',
				'remote_params' => ['type' => Biography::AUTHOR],
				'class' => Biography::class,
				'req_params' => ['locale' => 'parent.children[language]'],
				'page_limit' => 10,
				'primary_key' => 'id',
				'text_property' => 'title',
				'allow_clear' => true,
				'delay' => 250,
				'cache' => true,
				'cache_timeout' => 60000, // if 'cache' is true
				'language' => $locale,
				'placeholder' => 'main.field.ChooseAnOption'
			])
			->add('type', ChoiceType::class, [
				'label' => 'admin.source.Type',
				'required' => true,
				'choices'  => [
					 Source::BOOK_CANONICAL => Source::BOOK,
					 Source::MOVIE_CANONICAL => Source::MOVIE,
					 Source::TV_SERIES_CANONICAL => Source::TV_SERIES
				],
			])
			->add('fileManagement', FileManagementSelectorType::class, ["label" => "admin.source.Photo", "required" => false, "folder" => Source::FOLDER])
            ->add('save', SubmitType::class, array('label' => 'admin.main.Save', 'attr' => array('class' => 'btn btn-success')));
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"data_class" => Source::class,
			"locale" => null
		));
	}
	
    public function getName()
    {
        return 'source';
    }
}