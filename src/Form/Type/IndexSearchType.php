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

use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

use App\Entity\Biography;
use App\Entity\Country;
use App\Entity\Source;

class IndexSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$locale = $options["locale"];

        $builder
			->add('text', TextType::class, array("label" => "main.field.Keywords", "required" => false, "attr" => array("class" => "tagit full_width")))
			->add('type', ChoiceType::class, [
				"label" => "main.field.Type",
				'required' => false,
				'choices'  => [
					Biography::AUTHOR_CANONICAL => Biography::AUTHOR,
					Biography::FICTIONAL_CHARACTER_CANONICAL => Biography::FICTIONAL_CHARACTER,
					"main.field.YourQuotations" => "yourQuotations"
				],
			])
			
			->add('source', TextType::class, array("label" => "main.field.Source", "required" => false))
			->add('biography', TextType::class, array("label" => "main.field.Author", "required" => false))
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