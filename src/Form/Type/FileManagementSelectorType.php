<?php

namespace App\Form\Type;

use App\Form\DataTransformer\FileManagementTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\FileManagement;

class FileManagementSelectorType extends AbstractType
{
    private $transformer;
	private $entityManager;

    public function __construct(FileManagementTransformer $transformer, EntityManagerInterface $entityManager)
    {
        $this->transformer = $transformer;
		$this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->addModelTransformer($this->transformer);

        $builder
            ->add('id', TextType::class)
            ->add('filename', TextType::class)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
		if(!empty($id = $view->vars["value"]["id"]) and empty($view->vars["value"]["filename"])) {
			$fm = $this->entityManager->getRepository(FileManagement::class)->find($id);
			
			if(!empty($fm))
				$view->vars["value"]["filename"] = $fm->getPhoto();
		}

		$view->vars = array_merge($view->vars, array('folder' => $options['folder']));
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"folder" => null
		));
	}
}