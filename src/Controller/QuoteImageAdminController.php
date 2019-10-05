<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Entity\QuoteImage;
use App\Service\GenericFunction;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\TranslatorInterface;

class QuoteImageAdminController extends Controller
{
	public function indexAction(Request $request)
	{
		return $this->render('QuoteImage/index.html.twig');
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
		$entities = $entityManager->getRepository(QuoteImage::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $entityManager->getRepository(QuoteImage::class)->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$row[] = "<img class='mx-auto d-block' src='/photo/quote/".$entity->getImage()."'>";
			
			$socialNetworkArray = [];
			
			if(!empty($entity->getSocialNetwork()))
			{
				$ocialNetworks = array_unique($entity->getSocialNetwork());
				
				foreach ($ocialNetworks as $sn) {
					$socialNetworkArray[] = '<span class="badge badge-secondary"><i class="fab fa-'.strtolower($sn).'" aria-hidden="true"></i></span>';
				}
			}
			
			$row[] = empty($sn = $socialNetworkArray) ? "-" : implode(" ", $socialNetworkArray);
			$show = $this->generateUrl('quoteadmin_show', array('id' => $entity->getQuote()->getId()));
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans('admin.index.Read').'</a>';
			

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
}