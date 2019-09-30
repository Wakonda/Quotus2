<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Language;
use App\Form\Type\TagType;
use App\Service\GenericFunction;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;

class TagAdminController extends Controller
{
	public function indexAction(Request $request)
	{
		return $this->render('Tag/index.html.twig');
	}

	public function indexDatatablesAction(Request $request, TranslatorInterface $translator)
	{
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

		$entityManager = $this->getDoctrine()->getManager();
		$entities = $entityManager->getRepository(Tag::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $entityManager->getRepository(Tag::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

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
			
			$show = $this->generateUrl('tagadmin_show', array('id' => $entity->getId()));
			$edit = $this->generateUrl('tagadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans('admin.index.Read').'</a> - <a href="'.$edit.'" alt="Edit">'.$translator->trans('admin.index.Update').'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

    public function newAction(Request $request)
    {
		$entity = new Tag();
		$entityManager = $this->getDoctrine()->getManager();
		$entity->setLanguage($entityManager->getRepository(Language::class)->findOneBy(["abbreviation" => $request->getLocale()]));
        $form = $this->genericCreateForm($request->getLocale(), $entity);

		return $this->render('Tag/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, TranslatorInterface $translator)
	{
		$entity = new Tag();
        $form = $this->genericCreateForm($request->getLocale(), $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form);

		if($entity->getPhoto() == null)
			$form->get("photo")->addError(new FormError($translator->trans("This value should not be blank.", array(), "validators")));
		
		if($form->isValid())
		{
			$entityManager = $this->getDoctrine()->getManager();
			$gf = new GenericFunction();
			$photo = $gf->getUniqCleanNameForFile($entity->getPhoto());
			$entity->getPhoto()->move("photo/tag/", $photo);
			$entity->setPhoto($photo);
			$entityManager->persist($entity);
			$entityManager->flush();

			$redirect = $this->generateUrl('tagadmin_show', array('id' => $entity->getId()));

			return $this->redirect($redirect);
		}
		
		return $this->render('Tag/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Tag::class)->find($id);
	
		return $this->render('Tag/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Tag::class)->find($id);
		$form = $this->genericCreateForm($request->getLocale(), $entity);
	
		return $this->render('Tag/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Tag::class)->find($id);
		$currentImage = $entity->getPhoto();
		$form = $this->genericCreateForm($request->getLocale(), $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form);
		
		if($form->isValid())
		{
			if(!is_null($entity->getPhoto()))
			{
				$gf = new GenericFunction();
				$image = $gf->getUniqCleanNameForFile($entity->getPhoto());
				$entity->getPhoto()->move("photo/tag/", $image);
			}
			else
				$image = $currentImage;

			$entity->setPhoto($image);
			$entityManager->persist($entity);
			$entityManager->flush();

			$redirect = $this->generateUrl('tagadmin_show', array('id' => $entity->getId()));

			return $this->redirect($redirect);
		}
	
		return $this->render('Tag/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function getTagsByAjaxAction(Request $request)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$locale = $request->query->get("locale", null);
		$type = $request->query->get("type", null);
		$query = $request->query->get("q", null);
		
		$datas =  $entityManager->getRepository(Tag::class)->getDatasSelect($type, $locale, $query, null);
		
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
		return $this->createForm(TagType::class, $entity, array('locale' => $locale));
	}

	private function checkForDoubloon($entity, $form)
	{
		if($entity->getTitle() != null)
		{
			$entityManager = $this->getDoctrine()->getManager();
			$checkForDoubloon = $entityManager->getRepository(Tag::class)->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("title")->addError(new FormError('Cette entrée existe déjà !'));
		}
	}
}