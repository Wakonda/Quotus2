<?php

namespace App\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Proverb;
use App\Form\Type\CommentType;

class CommentController extends Controller
{
    public function indexAction(Request $request, $id)
    {
		$entity = new Comment();
        $form = $this->createForm(CommentType::class, $entity);

        return $this->render('Comment/index.html.twig', array('id' => $id, 'form' => $form->createView()));
    }
	
	public function createAction(Request $request, TokenStorageInterface $tokenStorage, TranslatorInterface $translator, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entity = new Comment();
        $form = $this->createForm(CommentType::class, $entity);
		$form->handleRequest($request);

		$user = $tokenStorage->getToken()->getUser();
		
		if(!empty($user) and is_object($user))
			$user = $entityManager->getRepository(User::class)->findByUsernameOrEmail($user->getUsername());
		else
		{
			$form->get("text")->addError(new FormError($translator->trans("comment.field.YouMustBeLoggedInToWriteAComment")));
		}

		if($form->isValid())
		{
			$entity->setUser($user);
			$entity->setProverb($entityManager->getRepository(Proverb::class)->find($id));

			$entityManager->persist($entity);
			$entityManager->flush();
			
			$entities = $entityManager->getRepository(Comment::class)->findAll();

			$form = $this->createForm(CommentType::class, new Comment());
		}

		$params = $this->getParametersComment($request, $id);

		return $this->render('Comment/form.html.twig', array("form" => $form->createView()));
	}
	
	public function loadCommentAction(Request $request, $id)
	{
		return $this->render('Comment/list.html.twig', $this->getParametersComment($request, $id));
	}
	
	private function getParametersComment($request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$max_comment_by_page = 7;
		$page = $request->query->get("page");
		$totalComments = $entityManager->getRepository(Comment::class)->countAllComments($id);
		$number_pages = ceil($totalComments / $max_comment_by_page);
		$first_message_to_display = ($page - 1) * $max_comment_by_page;
		
		$entities = $entityManager->getRepository(Comment::class)->displayComments($id, $max_comment_by_page, $first_message_to_display);
		
		return array("entities" => $entities, "page" => $page, "number_pages" => $number_pages);
	}
}
