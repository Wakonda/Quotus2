<?php

namespace App\Form\Type;

use App\Form\DataTransformer\BiographyTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use App\Service\GenericFunction;

class FileSelectorType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, array('data_class' => null, "required" => false))
            ->add('name', TextType::class, array("required" => false))->setDataMapper($this);
    }
	
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['current_file'] = $options['current_file'];
        $view->vars['path_file'] = $options['path_file'];
    }

    public function mapDataToForms($viewData, $forms)
    {
        if (null === $viewData) {
            return;
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['file']->setData($viewData);
    }

    public function mapFormsToData($forms, &$viewData)
    {
        $forms = iterator_to_array($forms);
		
		$gf = new GenericFunction();
		$content = null;
		$title = null;
	
		if(!empty($photo = $forms["file"]->getData())) {
			$title = $gf->getUniqCleanNameForFile($photo);
			$content = file_get_contents($photo->getPathname());
		} elseif (!empty($filename = $forms["name"]->getData())) {
			$title = uniqid().".".pathinfo($filename, PATHINFO_EXTENSION);
			$content = $gf->getContentURL($filename);
		}

		$viewData = ["title" => $title, "content" => $content];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'current_file' => null,
            'path_file' => null
        ]);
	}
}