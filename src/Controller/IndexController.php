<?php

namespace App\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Translation\TranslatorInterface;

use App\Form\Type\IndexSearchType;
use App\Service\Captcha;
use App\Service\Gravatar;
use App\Service\Pagination;

use App\Entity\Country;
use App\Entity\Page;
use App\Entity\Store;
use App\Entity\Quote;
use App\Entity\Language;
use App\Entity\Biography;

use Spipu\Html2Pdf\Html2Pdf;
use MatthiasMullie\Minify;


class IndexController extends Controller
{
    public function indexAction(Request $request, \Swift_Mailer $mailer)
    {
		$entityManager = $this->getDoctrine()->getManager();
		$random = $entityManager->getRepository(Quote::class)->getRandom($request->getLocale());

        return $this->render('Index/index.html.twig', ['random' => $random]);
    }

	public function readAction(Request $request, $id, $idImage)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(Quote::class)->find($id);
		$image = (!empty($idImage)) ? $entityManager->getRepository(QuoteImage::class)->find($idImage) : null;
		
		$browsing = $entityManager->getRepository(Quote::class)->browsingShow($id);

		return $this->render('Index/read.html.twig', array('entity' => $entity, 'browsing' => $browsing, 'image' => $image));
	}

	public function pageAction(Request $request, $name)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$language = $entityManager->getRepository(Language::class)->findOneBy(['abbreviation' => $request->getLocale()]);
		$entity = $entityManager->getRepository(Page::class)->findOneBy(["internationalName" => $name, "language" => $language]);
		
		return $this->render('Index/page.html.twig', array("entity" => $entity));
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
}