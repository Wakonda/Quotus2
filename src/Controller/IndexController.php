<?php

namespace App\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Form\Type\IndexSearchType;
use App\Service\Captcha;
use App\Service\Gravatar;
use App\Service\Pagination;

use App\Entity\Country;
use App\Entity\Page;
use App\Entity\Store;
use App\Entity\Quote;
use App\Entity\Source;
use App\Entity\QuoteImage;
use App\Entity\Language;
use App\Entity\Biography;

use Spipu\Html2Pdf\Html2Pdf;
use MatthiasMullie\Minify;


class IndexController extends Controller
{
    public function indexAction(Request $request, \Swift_Mailer $mailer)
    {
		$entityManager = $this->getDoctrine()->getManager();

		$form = $this->createFormIndexSearch($request->getLocale(), null);
		$random = $entityManager->getRepository(Quote::class)->getRandom($request->getLocale());

        return $this->render('Index/index.html.twig', ['form' => $form->createView(), 'random' => $random]);
    }

	public function changeLanguageAction(Request $request, $locale)
	{
		$request->getSession()->set('_locale', $locale);
		return $this->redirect($this->generateUrl('index'));
	}

	public function indexSearchAction(Request $request, TranslatorInterface $translator)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$search = $request->request->get("index_search");
		
		unset($search["_token"]);

		$criteria = $search;
		
		if($search['type'] == Biography::AUTHOR)
			$criteria['type'] = $translator->trans(Biography::AUTHOR_CANONICAL);
		elseif($search['type'] == Biography::FICTIONAL_CHARACTER)
			$criteria['type'] = $translator->trans(Biography::FICTIONAL_CHARACTER_CANONICAL);
		
		// $criteria['country'] = (empty($search['country'])) ? null : $entityManager->getRepository(Country::class)->find($search['country'])->getTitle();
		$criteria = array_filter(array_values($criteria));
		$criteria = empty($criteria) ? $translator->trans("search.result.None") : $criteria;

		return $this->render('Index/resultIndexSearch.html.twig', ['search' => base64_encode(json_encode($search)), 'criteria' => $criteria]);
	}

	public function indexSearchDatatablesAction(Request $request, $search)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i < intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}
		$sSearch = json_decode(base64_decode($search));
		$entities = $entityManager->getRepository(Quote::class)->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale());
		$iTotal = $entityManager->getRepository(Quote::class)->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale(), true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $this->generateUrl('read', array('id' => $entity->getId(), 'slug' => $entity->getSlug()));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity->getText().'</a>';
			$row[] = $entity->isBiographyAuthorType() ? $entity->getBiography()->getTitle() : $entity->getUser()->getUsername();

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function readAction(Request $request, $id, $idImage)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Quote::class)->find($id);
		$image = (!empty($idImage)) ? $entityManager->getRepository(QuoteImage::class)->find($idImage) : null;
		
		$browsing = $entityManager->getRepository(Quote::class)->browsingShow($id);

		return $this->render('Index/read.html.twig', array('entity' => $entity, 'browsing' => $browsing, 'image' => $image));
	}

	public function readPDFAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Quote::class)->find($id, true);
		
		if(empty($entity))
			throw $this->createNotFoundException('404');
		
		$content = $this->renderView('Index/pdf_quote.html.twig', array('entity' => $entity));

		$html2pdf = new Html2Pdf('P','A4','fr');
		$html2pdf->WriteHTML($content);
		$file = $html2pdf->Output('quote.pdf');

		$response = new Response($file);
		$response->headers->set('Content-Type', 'application/pdf');

		return $response;
	}

	public function byImagesAction(Request $request)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$query = $entityManager->getRepository(QuoteImage::class)->getPaginator($request->getLocale());
		
		$paginator  = $this->get('knp_paginator');
		$pagination = $paginator->paginate(
			$query, /* query NOT result */
			$request->query->getInt('page', 1), /*page number*/
			10 /*limit per page*/
		);
		
		$pagination->setCustomParameters(['align' => 'center']);
		
		return $this->render('Index/byimage.html.twig', ['pagination' => $pagination]);
	}

	// BY SOURCES
	public function bySourcesAction(Request $request)
    {
        return $this->render('Index/bysource.html.twig');
    }
	
	public function bySourcesDatatablesAction(Request $request)
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
		$entities = $entityManager->getRepository(Quote::class)->findQuoteBySource($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale());
		$iTotal = $entityManager->getRepository(Quote::class)->findQuoteBySource($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale(), true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $this->generateUrl('source', array('id' => $entity['source_id'], 'slug' => $entity['source_slug']));
			$row[] = '<img src="'.$request->getBaseUrl().'/photo/source/'.$entity['source_photo'].'"/>';
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['source_title'].'</a>';

			$row[] = '<span class="badge badge-secondary">'.$entity['number_by_source'].'</span>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	// SOURCE
	public function sourceAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Source::class)->find($id);

		return $this->render('Index/source.html.twig', array('entity' => $entity));
	}

	public function sourceDatatablesAction(Request $request, TranslatorInterface $translator, $sourceId)
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
		$entities = $entityManager->getRepository(Quote::class)->getQuoteBySourceDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $sourceId);
		$iTotal = $entityManager->getRepository(Quote::class)->getQuoteBySourceDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $sourceId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity["quote_text"];
			$show = $this->generateUrl('read', array('id' => $entity["quote_id"], 'slug' => $entity["quote_slug"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans("source.table.Read").'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	////////////////////
	// BY AUTHORS
	public function byAuthorsAction(Request $request)
    {
        return $this->render('Index/byauthor.html.twig');
    }
	
	public function byAuthorsDatatablesAction(Request $request)
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
		$entities = $entityManager->getRepository(Quote::class)->findQuoteByBiography(Biography::AUTHOR, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale());
		$iTotal = $entityManager->getRepository(Quote::class)->findQuoteByBiography(Biography::AUTHOR, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale(), true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $this->generateUrl('author', array('id' => $entity['biography_id'], 'slug' => $entity['biography_slug']));
			$row[] = '<img src="'.$request->getBaseUrl().'/photo/biography/'.$entity['source_photo'].'"/>';
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['biography_title'].'</a>';

			$row[] = '<span class="badge badge-secondary">'.$entity['number_by_biography'].'</span>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	// AUTHOR
	public function authorAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Biography::class)->find($id);
		$stores = $entityManager->getRepository(Store::class)->findBy(["biography" => $entity]);

		return $this->render('Index/author.html.twig', array('entity' => $entity, 'stores' => $stores));
	}

	public function authorDatatablesAction(Request $request, TranslatorInterface $translator, $biographyId)
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
		$entities = $entityManager->getRepository(Quote::class)->getQuoteByBiographyDatatables(Biography::AUTHOR, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $biographyId);
		$iTotal = $entityManager->getRepository(Quote::class)->getQuoteByBiographyDatatables(Biography::AUTHOR, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $biographyId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity["quote_text"];
			$row[] = !empty($entity["source_id"]) ? '<u><a href="'.$this->generateUrl("source", ['id' => $entity["source_id"], 'slug' => $entity["source_slug"]]).'">'.$entity["source_text"].'</a></u>' : "-";
			$show = $this->generateUrl('read', array('id' => $entity["quote_id"], 'slug' => $entity["quote_slug"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans("biography.table.Read").'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	////////////////////
	// BY FICTIONALCHARACTERS
	public function byFictionalCharactersAction(Request $request)
    {
        return $this->render('Index/byfictionalcharacter.html.twig');
    }
	
	public function byFictionalCharactersDatatablesAction(Request $request)
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
		$entities = $entityManager->getRepository(Quote::class)->findQuoteByBiography(Biography::FICTIONAL_CHARACTER, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale());
		$iTotal = $entityManager->getRepository(Quote::class)->findQuoteByBiography(Biography::FICTIONAL_CHARACTER, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale(), true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $this->generateUrl('fictionalcharacter', array('id' => $entity['biography_id'], 'slug' => $entity['biography_slug']));
			$row[] = '<img src="'.$request->getBaseUrl().'/photo/biography/'.$entity['biography_photo'].'"/>';
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['biography_title'].'</a>';

			$row[] = '<span class="badge badge-secondary">'.$entity['number_by_biography'].'</span>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	// FICTIONALCHARACTER
	public function fictionalCharacterAction(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Biography::class)->find($id);

		return $this->render('Index/fictionalCharacter.html.twig', array('entity' => $entity));
	}

	public function fictionalCharacterDatatablesAction(Request $request, TranslatorInterface $translator, $biographyId)
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
		$entities = $entityManager->getRepository(Quote::class)->getQuoteByBiographyDatatables(Biography::FICTIONAL_CHARACTER, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $biographyId);
		$iTotal = $entityManager->getRepository(Quote::class)->getQuoteByBiographyDatatables(Biography::FICTIONAL_CHARACTER, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $biographyId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity["quote_text"];
			$row[] = !empty($entity["source_id"]) ? '<u><a href="'.$this->generateUrl("source", ['id' => $entity["source_id"], 'slug' => $entity["source_slug"]]).'">'.$entity["source_text"].'</a></u>' : "-";
			$show = $this->generateUrl('read', array('id' => $entity["quote_id"], 'slug' => $entity["quote_slug"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$translator->trans("fictionalCharacter.table.Read").'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	public function byUsersAction(Request $request)
    {
        return $this->render('Index/byuser.html.twig');
    }

	public function byUsersDatatablesAction(Request $request)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i < intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $entityManager->getRepository(Quote::class)->findQuoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale());
		$iTotal = $entityManager->getRepository(Quote::class)->findQuoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $request->getLocale(), true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			if(!empty($entity['id']))
			{
				$row = array();

				$show = $this->generateUrl('read', array('id' => $entity['quote_id'], 'slug' => $entity['slug']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['quote_title'].'</a>';

				$show = $this->generateUrl('user_show', array('username' => $entity['username']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['username'].'</a>';

				$output['aaData'][] = $row;
			}
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function downloadImageAction($fileName)
	{
		$response = new BinaryFileResponse('photo/quote/'.$fileName);
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);
		return $response;
	}

    public function storeAction(Request $request, Pagination $pagination, $page)
    {
		$em = $this->getDoctrine()->getManager();

		$query = $request->request->get("query", null);
		$page = (empty(intval($page))) ? 1 : $page;
		$nbMessageByPage = 12;
		
		$entities = $em->getRepository(Store::class)->getProducts($nbMessageByPage, $page, $query, $request->getLocale());
		$totalEntities = $em->getRepository(Store::class)->getProducts(0, 0, $query, $request->getLocale(), true);
		
		$links = $pagination->setPagination(['url' => 'store'], $page, $totalEntities, $nbMessageByPage);

		return $this->render('Index/store.html.twig', array(
			'entities' => $entities,
			'page' => $page,
			'query' => $query,
			'links' => $links
		));
    }

	public function readStoreAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		$entity = $em->getRepository(Store::class)->find($id);
		
		return $this->render('Index/readStore.html.twig', [
			'entity' => $entity
		]);
	}

	public function pageAction(Request $request, $name)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$language = $entityManager->getRepository(Language::class)->findOneBy(['abbreviation' => $request->getLocale()]);
		$entity = $entityManager->getRepository(Page::class)->findOneBy(["internationalName" => $name, "language" => $language]);
		
		return $this->render('Index/page.html.twig', array("entity" => $entity));
	}

	public function lastAction(Request $request)
    {
		$entityManager = $this->getDoctrine()->getManager();
		$entities = $entityManager->getRepository(Quote::class)->getLastEntries($request->getLocale());

		return $this->render('Index/last.html.twig', array('entities' => $entities));
    }

	public function statAction(Request $request)
    {
		$entityManager = $this->getDoctrine()->getManager();
		$statistics = $entityManager->getRepository(Quote::class)->getStat($request->getLocale());

		return $this->render('Index/stat.html.twig', array('statistics' => $statistics));
    }

	public function reloadCaptchaAction(Request $request)
	{
		$captcha = new Captcha($request->getSession());

		$wordOrNumberRand = rand(1, 2);
		$length = rand(3, 7);

		if($wordOrNumberRand == 1)
			$word = $captcha->wordRandom($length);
		else
			$word = $captcha->numberRandom($length);

		$response = new Response(json_encode(array("new_captcha" => $captcha->generate($word))));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function reloadGravatarAction(Request $request)
	{
		$gr = new Gravatar();

		$response = new Response(json_encode(array("new_gravatar" => $gr->getURLGravatar())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	private function createFormIndexSearch($locale, $entity)
	{
		return $this->createForm(IndexSearchType::class, null, ["locale" => $locale]);
	}
}