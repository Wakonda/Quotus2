<?php

namespace App\Controller;

use App\Entity\Source;
use App\Entity\User;
use App\Entity\Biography;
use App\Entity\Language;
use App\Form\Type\SourceType;
use App\Service\GenericFunction;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;

class SourceAdminController extends Controller
{
	private $formName = "source";

	public function indexAction(Request $request)
	{
		return $this->render('Source/index.html.twig');
	}

	public function indexDatatablesAction(Request $request, TranslatorInterface $translator)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}
		
		$entities = $entityManager->getRepository(Source::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $entityManager->getRepository(Source::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity->getId();
			$row[] = $entity->getTitle();
			$row[] = $entity->getLanguage()->getTitle();
			
			$show = $this->generateUrl('sourceadmin_show', array('id' => $entity->getId()));
			$edit = $this->generateUrl('sourceadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans('admin.index.Read').'</a> - <a href="'.$edit.'" alt="Edit">'.$translator->trans('admin.index.Update').'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

    public function newAction(Request $request)
    {
		$entity = new Source();
		
		$entityManager = $this->getDoctrine()->getManager();
		$language = $entityManager->getRepository(Language::class)->findOneBy(["abbreviation" => $request->getLocale()]);
		
		$entity->setLanguage($language);

        $form = $this->genericCreateForm($request->getLocale(), $entity);

		return $this->render('Source/new.html.twig', array('form' => $form->createView()));
    }

	public function createAction(Request $request, TranslatorInterface $translator)
	{
		$entity = new Source();
		$entityManager = $this->getDoctrine()->getManager();
		$locale = $request->request->get($this->formName)["language"];
		$language = $entityManager->getRepository(Language::class)->find($locale);

        $form = $this->genericCreateForm($language->getAbbreviation(), $entity);
		$form->handleRequest($request);

		$this->checkForDoubloon($translator, $entity, $form);

		if($entity->getPhoto() == null)
			$form->get("photo")->addError(new FormError($translator->trans("This value should not be blank.", array(), "validators")));

		if($form->isValid())
		{
			$entityManager->persist($entity);
			
			$gf = new GenericFunction();
			$image = $gf->getUniqCleanNameForFile($entity->getPhoto());
			$entity->getPhoto()->move("photo/source/", $image);
			$entity->setPhoto($image);
			
			$entityManager->flush();

			$redirect = $this->generateUrl('sourceadmin_show', array('id' => $entity->getId()));

			return $this->redirect($redirect);
		}
		
		return $this->render('Source/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Source::class)->find($id);
	
		return $this->render('Source/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Source::class)->find($id);
		$form = $this->genericCreateForm($entity->getLanguage()->getAbbreviation(), $entity);

		return $this->render('Source/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, TranslatorInterface $translator, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Source::class)->find($id);
		
		$locale = $request->request->get($this->formName)["language"];
		$language = $entityManager->getRepository(Language::class)->find($locale);
		
		$currentImage = $entity->getPhoto();
		$form = $this->genericCreateForm($language->getAbbreviation(), $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($translator, $entity, $form);
		
		if($form->isValid())
		{
			if(!is_null($entity->getPhoto()))
			{
				$gf = new GenericFunction();
				$image = $gf->getUniqCleanNameForFile($entity->getPhoto());
				$entity->getPhoto()->move("photo/source/", $image);
			}
			else
				$image = $currentImage;

			$entity->setPhoto($image);

			$entityManager->persist($entity);
			$entityManager->flush();

			return $this->redirect($this->generateUrl('sourceadmin_show', array('id' => $entity->getId())));
		}
	
		return $this->render('Source/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function getBiographiesByAjaxAction(Request $request)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$locale = $request->query->get("locale", null);
		$type = $request->query->get("type", null);
		$query = $request->query->get("q", null);
		
		$datas =  $entityManager->getRepository(Biography::class)->getDatasSelect($type, $locale, $query, null);
		
		$res = [];
		
		foreach($datas as $data)
		{
			$row = [];
			
			$row["id"] = $data->getId();
			$row["text"] = $data->getTitle();
			
			$res["results"][] = $row;
		}
		
		$response = new Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	private function genericCreateForm($locale, $entity)
	{
		return $this->createForm(SourceType::class, $entity, array('locale' => $locale));
	}

	private function checkForDoubloon(TranslatorInterface $translator, $entity, $form)
	{
		if($entity->getTitle() != null)
		{
			$entityManager = $this->getDoctrine()->getManager();
			$checkForDoubloon = $entityManager->getRepository(Source::class)->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("title")->addError(new FormError($translator->trans("admin.index.ThisEntryAlreadyExists")));
		}
	}
}