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

use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

use App\Repository\LanguageRepository;
use App\Entity\Biography;
use App\Entity\Source;
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
		   ->add('source', Select2EntityType::class, [
				'label' => 'admin.store.Source',
				'multiple' => false,
				'remote_route' => 'quoteadmin_getsourcesbyajax',
				'class' => Source::class,
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
		   ->add('biography', Select2EntityType::class, [
				'label' => 'admin.store.Biography',
				'multiple' => false,
				'remote_route' => 'quoteadmin_getbiographiesbyajax',
				'class' => Biography::class,
				'req_params' => ['source' => 'parent.children[source]'],
				'page_limit' => 10,
				'primary_key' => 'id',
				'text_property' => 'title',
				'allow_clear' => true,
				'delay' => 250,
				'cache' => true,
				'constraints' => new Assert\NotBlank(),
				'required' => true,
				'cache_timeout' => 60000, // if 'cache' is true
				'language' => $locale,
				'placeholder' => 'main.field.ChooseAnOption'
			])
			->add('newBiography', HiddenType::class, array("mapped" => false))
            ->add('text', TextareaType::class, array( 'attr' => array('class' => 'redactor'), 
                'required' => false, "label" => "admin.store.Text"
            ))
			->add('embedCode', TextareaType::class, array('constraints' => new Assert\NotBlank(), "label" => "admin.store.EmbedCode", "required" => true))
			// ->add('photo', FileSelectorType::class, array("label" => "admin.store.Image", "required" => true, "current_file" => $builder->getData()->getPhoto(), "path_file" => Store::PATH_FILE))
			->add('amazonCode', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "admin.store.ProductCode", 'attr' => array('class' => 'redactor')
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