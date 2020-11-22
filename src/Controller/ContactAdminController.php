<?php

namespace App\Controller;

use App\Entity\Contact;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactAdminController extends AbstractController
{
	public function indexAction(Request $request)
	{
		return $this->render('Contact/index.html.twig');
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
		$entities = $entityManager->getRepository(Contact::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $entityManager->getRepository(Contact::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

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
			$row[] = $entity->getSubject();
			$row[] = $entity->getDateSending()->format("d/m/Y H:i");
			
			if($entity->getReadMessage() == 1)
				$row[] = '<i class="fas fa-check text-success"></i>';
			else
				$row[] = '<i class="fas fa-times text-danger"></i>';
			
			$show = $this->generateUrl('contactadmin_show', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans("admin.index.Read").'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	public function showAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Contact::class)->find($id);
		
		$entity->setReadMessage(true);
		$entityManager->persist($entity);
		$entityManager->flush();

		return $this->render('Contact/show.html.twig', array('entity' => $entity));
	}
}