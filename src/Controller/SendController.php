<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\Type\SendType;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Quote;

class SendController extends AbstractController
{
    public function indexAction(Request $request, $id)
    {
		$form = $this->createForm(SendType::class, null);

        return $this->render('Index/send.html.twig', array('form' => $form->createView(), 'id' => $id));
    }
	
	public function sendAction(Request $request, \Swift_Mailer $mailer, $id)
	{
		parse_str($request->request->get('form'), $form_array);

        $form = $this->createForm(SendType::class, $form_array);
		
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid())
		{
			$data = (object)($request->request->get($form->getName()));
			$entityManager = $this->getDoctrine()->getManager();
			$entity = $entityManager->getRepository(Quote::class)->find($id);
		
			$content = $this->renderView('Index/send_message_content.html.twig', array(
				"data" => $data,
				"entity" => $entity
			));

			$message = (new \Swift_Message($data->subject))
				->setFrom('quotus@wakonda.guru', "Quotus")
				->setTo($data->recipientMail)
				->setBody($content, 'text/html');
		
			$mailer->send($message);
			
			$response = new Response(json_encode(array("result" => "ok")));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
		}

		$res = array("result" => "error");
		
		$res["content"] = $this->render('Index/send_form.html.twig', array('form' => $form->createView(), 'id' => $id))->getContent();

		$response = new Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
}