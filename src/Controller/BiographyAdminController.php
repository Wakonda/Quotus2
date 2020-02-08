<?php

namespace App\Controller;

use App\Entity\Biography;
use App\Entity\Language;
use App\Form\Type\BiographyType;
use App\Service\GenericFunction;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class BiographyAdminController extends AbstractController
{
	private $formName = "biography";

	public function indexAction()
	{
		return $this->render('Biography/index.html.twig');
	}

	public function indexDatatablesAction(Request $request, TranslatorInterface $translator)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');
		$state = $request->query->get('state');

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
		
		$entities = $entityManager->getRepository(Biography::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $state);
		$iTotal = $entityManager->getRepository(Biography::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $state, true);

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
			
			$show = $this->generateUrl('biographyadmin_show', array('id' => $entity->getId()));
			$edit = $this->generateUrl('biographyadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans('admin.index.Read').'</a> - <a href="'.$edit.'" alt="Edit">'.$translator->trans('admin.index.Update').'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

    public function newAction(Request $request)
    {
		$entityManager = $this->getDoctrine()->getManager();
		$entity = new Biography();
		$entity->setLanguage($entityManager->getRepository(Language::class)->findOneBy(["abbreviation" => $request->getLocale()]));

        $form = $this->genericCreateForm($request->getLocale(), $entity);

		return $this->render('Biography/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, TranslatorInterface $translator)
	{
		$entity = new Biography();
		$locale = $request->request->get($this->formName)["language"];
		$language = $this->getDoctrine()->getManager()->getRepository(Language::class)->find($locale);

        $form = $this->genericCreateForm($language->getAbbreviation(), $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($translator, $entity, $form);

		if($form->isValid())
		{
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($entity);
			$entityManager->flush();

			return $this->redirect($this->generateUrl('biographyadmin_show', array('id' => $entity->getId())));
		}
		
		return $this->render('Biography/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Biography::class)->find($id);
	
		return $this->render('Biography/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Biography::class)->find($id);
		$form = $this->genericCreateForm($entity->getLanguage()->getAbbreviation(), $entity);
	
		return $this->render('Biography/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, TranslatorInterface $translator, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Biography::class)->find($id);
		
		$locale = $request->request->get($this->formName)["language"];
		$language = $entityManager->getRepository(Language::class)->find($locale);

		$form = $this->genericCreateForm($language->getAbbreviation(), $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($translator, $entity, $form);
		
		if($form->isValid())
		{
			$entityManager->persist($entity);
			$entityManager->flush();

			return $this->redirect($this->generateUrl('biographyadmin_show', array('id' => $entity->getId())));
		}
	
		return $this->render('Biography/edit.html.twig', ['form' => $form->createView(), 'entity' => $entity]);
	}

	public function getBiographiesByAjaxAction(Request $request)
	{
		$locale = $request->query->get("locale");
		$entityManager = $this->getDoctrine()->getManager();
		$rsp = new Response();
		$rsp->headers->set('Content-Type', 'application/json');
		
		if($request->query->has("pkey_val")) {
			$pkeyVal = $request->query->has("pkey_val");
			
			if(empty($pkeyVal))
			{
				$rsp->setContent([]);
				return $rsp;
			}

			$parameters = array("pkey_val" => $request->query->get("pkey_val"));
			$response =  $entityManager->getRepository(Biography::class)->getDatasCombobox($parameters, $locale);

			$resObj = new \stdClass();
			$resObj->id = $response["id"];
			$resObj->name = $response["title"];

			$rsp->setContent(json_encode($resObj));
			return $rsp;
		}

		$parameters = array(
		  'db_table'     => $request->query->get('db_table'),
		  'page_num'     => $request->query->get('page_num'),
		  'per_page'     => $request->query->get('per_page'),
		  'and_or'       => $request->query->get('and_or'),
		  'order_by'     => $request->query->get('order_by'),
		  'search_field' => $request->query->get('search_field'),
		  'q_word'       => $request->query->get('q_word')
		);

		$parameters['offset']  = ($parameters['page_num'] - 1) * $parameters['per_page'];

		$response =  $entityManager->getRepository(Biography::class)->getDatasCombobox($parameters, $locale);
		$count =  $entityManager->getRepository(Biography::class)->getDatasCombobox($parameters, $locale, true);

		$results = array();

		foreach($response as $res) {
			$obj = new \stdClass();
			$obj->id = $res['id'];
			$obj->name = $res['title'];
			
			$results[] = $obj;
		}

		$resObj = new \stdClass();
		$resObj->result = $results;
		$resObj->cnt_whole = $count;

		$rsp->setContent(json_encode($resObj));
		return $rsp;
	}
	
	private function genericCreateForm($locale, $entity)
	{
		return $this->createForm(BiographyType::class, $entity, array("locale" => $locale));
	}
	
	private function checkForDoubloon(TranslatorInterface $translator, $entity, $form)
	{
		if($entity->getTitle() != null)
		{
			$entityManager = $this->getDoctrine()->getManager();
			$checkForDoubloon = $entityManager->getRepository(Biography::class)->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("title")->addError(new FormError($translator->trans("admin.index.ThisEntryAlreadyExists")));
		}
	}
}