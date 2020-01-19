<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vote;
use App\Entity\Comment;
use App\Entity\Quote;
use App\Form\Type\UserType;
use App\Form\Type\UpdatePasswordType;
use App\Form\Type\ForgottenPasswordType;
use App\Form\Type\LoginType;

use App\Service\Mailer;
use App\Service\PasswordHash;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils, SessionInterface $session, TranslatorInterface $translator)
    {
		if($request->query->get("t") != null)
		{
			$entityManager = $this->getDoctrine()->getManager();
			$entity = $entityManager->getRepository(User::class)->findOneByToken($request->query->get("t"));
			
			$now = new \Datetime();

			if($entity->getExpiredAt() > $now)
			{
				$session->getFlashBag()->add('confirm_login', $translator->trans('user.login.CongratulationAccountActivated', ['%username%' => $entity->getUsername()]));
				$entity->setEnabled(true);
				$entityManager->persist($entity);
				$entityManager->flush();
			}
			else
				$session->getFlashBag()->add('expired_login', $translator->trans('user.login.AccountCannotBeActivated', ['%username%' => $entity->getUsername()]));
		}

		return $this->render('User/login.html.twig', array(
				'error'         => $authenticationUtils->getLastAuthenticationError(),
				'last_username' => $authenticationUtils->getLastUsername()
		));
    }

	public function listAction(Request $request)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$entities = $entityManager->getRepository(User::class)->findAll();

		return $this->render('User/list.html.twig', array('entities' => $entities));
	}

	public function showAction(TokenStorageInterface $tokenStorage, $username)
	{
		$entityManager = $this->getDoctrine()->getManager();
		if(!empty($username))
			$entity = $entityManager->getRepository(User::class)->findOneBy(["username" => $username]);
		else
			$entity = $tokenStorage->getToken()->getUser();

		return $this->render('User/show.html.twig', array('entity' => $entity));
	}

	public function newAction(Request $request)
	{
		$entity = new User();
        $form = $this->createFormUser($entity, false);

		return $this->render('User/new.html.twig', array('form' => $form->createView()));
	}

	public function createAction(Request $request, SessionInterface $session, \Swift_Mailer $mailer, TranslatorInterface $translator)
	{
		$entity = new User();
        $form = $this->createFormUser($entity, false);
		$form->handleRequest($request);
		
		$params = $request->request->get("user");

		if($params["captcha"] != "" and $session->get("captcha_word") != $params["captcha"])
			$form->get("captcha")->addError(new FormError($translator->trans('user.createAccount.TheWordMustMatchThePicture')));

		$this->checkForDoubloon($translator, $entity, $form);

		if($form->isValid())
		{
			if(!is_null($entity->getAvatar()))
			{
				$image = uniqid()."_avatar.png";
				$entity->getAvatar()->move("photo/user/", $image);
				$entity->setAvatar($image);
			}

			$ph = new PasswordHash();
			$salt = $ph->create_hash($entity->getPassword());
			
			$encoder = new MessageDigestPasswordEncoder();
			$entity->setPassword($encoder->encodePassword($entity->getPassword(), $salt));
			
			$expiredAt = new \Datetime();
			$entity->setExpiredAt($expiredAt->modify("+1 day"));
			$entity->setToken(md5(uniqid(mt_rand(), true).$entity->getUsername()));
			$entity->setEnabled(false);
			$entity->setSalt($salt);

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($entity);
			$entityManager->flush();

			// Send email
			$body = $this->renderView('User/confirmationInscription_mail.html.twig', array("entity" => $entity));

			$message = (new \Swift_Message('Quotus - '.$translator->trans('user.createAccount.Registration')))
				->setFrom('amatukami66@gmail.com', "Quotus")
				->setTo($entity->getEmail())
				->setBody($body, 'text/html');
		
			$mailer->send($message);

			return $this->render('User/confirmationInscription.html.twig', array('entity' => $entity));
		}

		return $this->render('User/new.html.twig', array('form' => $form->createView()));
	}

	public function editAction(Request $request, TokenStorageInterface $tokenStorage, TranslatorInterface $translator, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		if(!empty($id))
			$entity = $entityManager->getRepository(User::class)->find($id);
		else
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, $translator->trans('user.createAccount.UnableToAccessThisPage'));
			$entity = $tokenStorage->getToken()->getUser();
		}

		$form = $this->createFormUser($entity, true);
	
		return $this->render('User/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, TokenStorageInterface $tokenStorage, TranslatorInterface $translator, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		if(empty($id))
			$entity = $tokenStorage->getToken()->getUser();
		else
			$entity = $entityManager->getRepository(User::class)->find($id);
		
		$current_avatar = $entity->getAvatar();

		$form = $this->createFormUser($entity, true);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($translator, $entity, $form);

		if($form->isValid())
		{
			if(!is_null($entity->getAvatar()))
			{
				unlink("photo/user/".$current_avatar);
				$image = uniqid()."_avatar.png";
				$entity->getAvatar()->move("photo/user/", $image);
				$entity->setAvatar($image);
			}
			else
				$entity->setAvatar($current_avatar);

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($entity);
			$entityManager->flush();

			$redirect = $this->generateUrl('user_show', array('id' => $entity->getId()));

			return $this->redirect($redirect);
		}
	
		return $this->render('User/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function updatePasswordAction(Request $request, TokenStorageInterface $tokenStorage)
	{
		$entity = $tokenStorage->getToken()->getUser();
		$form = $this->createForm(UpdatePasswordType::class, $entity);
		
		return $this->render('User/updatepassword.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	public function updatePasswordSaveAction(Request $request, SessionInterface $session, TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
	{
		$entity = $tokenStorage->getToken()->getUser();
        $form = $this->createForm(UpdatePasswordType::class, $entity);
		$form->handleRequest($request);

		if($form->isValid())
		{
			$ph = new PasswordHash();
			$salt = $ph->create_hash($entity->getPassword());
			
			$encoder = new MessageDigestPasswordEncoder();
			$entity->setSalt($salt);
			$entity->setPassword($encoder->encodePassword($entity->getPassword(), $salt));
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($entity);
			$entityManager->flush();

			$session->getFlashBag()->add('new_password', $translator->trans('forgottenPassword.confirmation.YourPasswordHasBeenChanged'));

			return $this->redirect($this->generateUrl('user_show', array('id' => $id)));
		}
		
		return $this->render('User/updatepassword.html.twig', array('form' => $form->createView()));
	}
	
	public function forgottenPasswordAction(Request $request)
	{
		$form = $this->createForm(ForgottenPasswordType::class, null);
	
		return $this->render('User/forgottenpassword.html.twig', array('form' => $form->createView()));
	}
	
	public function forgottenPasswordSendAction(Request $request, \Swift_Mailer $mailer, TranslatorInterface $translator)
	{
        $form = $this->createForm(ForgottenPasswordType::class, null);
		$form->handleRequest($request);
	
		$params = $request->request->get("forgotten_password");

		if($params["captcha"] != "" and $request->getSession()->get("captcha_word") != $params["captcha"])
			$form->get("captcha")->addError(new FormError($translator->trans('forgottenPassword.field.TheWordMustMatchThePicture')));

		$entityManager = $this->getDoctrine()->getManager();
		$entity = $entityManager->getRepository(User::class)->findByUsernameOrEmail($params["emailUsername"]);

		if(empty($entity))
			$form->get("emailUsername")->addError(new FormError($translator->trans('forgottenPassword.field.UsernameOrEmailAddressDoesNotExist')));

		if(!$form->isValid())
		{
			return $this->render('User/forgottenpassword.html.twig', array('form' => $form->createView()));
		}
		
		$temporaryPassword = $this->randomPassword();
		$ph = new PasswordHash();
		$salt = $ph->create_hash($temporaryPassword);

		$encoder = new MessageDigestPasswordEncoder();
		$entity->setSalt($salt);
		$entity->setPassword($encoder->encodePassword($temporaryPassword, $salt));
		$entityManager->persist($entity);
        $entityManager->flush();
		
		// Send email
		$body = $this->renderView('User/forgottenpassword_mail.html.twig', array("entity" => $entity, "temporaryPassword" => $temporaryPassword));

		$message = (new \Swift_Message("Quotus - ".$translator->trans('forgottenPassword.index.ForgotYourPassword')))
			->setFrom('amatukami66@gmail.com', "Quotus")
			->setTo($entity->getEmail())
			->setBody($body, 'text/html');
	
		$mailer->send($message);
		
		return $this->render('User/forgottenpasswordsend.html.twig');
	}

	private function randomPassword($length = 8)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789&+-$";
		
		if($length >= strlen($chars))
			$length = 8;
		
		$password = substr(str_shuffle($chars), 0, $length);
		
		return $password;
	}

	private function createFormUser($entity, $ifEdit)
	{
		return $this->createForm(UserType::class, $entity, array('edit' => $ifEdit));
	}

	private function checkForDoubloon(TranslatorInterface $translator, $entity, $form)
	{
		if($entity->getUsername() != null)
		{
			$entityManager = $this->getDoctrine()->getManager();
			$checkForDoubloon = $entityManager->getRepository(User::class)->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("username")->addError(new FormError($translator->trans('user.createAccount.UserSameUsernameEmailExists')));
		}
	}
	
	private function createTemporaryPassword($email)
	{
		$key = strlen(uniqid());
		
		if(strlen($key) < strlen($email))
			$key = str_pad($key, strlen($email), $key, STR_PAD_RIGHT);
		elseif(strlen($key) > strlen($email))
		{
			$diff = strlen($key) - strlen($email);
			$key = substr($key, 0, -$diff);
		}
		
		return $email ^ $key;
	}

	private function testStrongestPassword(TranslatorInterface $translator, $form, $password)
	{
		$min_length = 5;
		
		$letter = array();
		$number = array();
		
		for($i = 0; $i < strlen($password); $i++)
		{
			$current = $password[$i];
			
			if(($current >= 'a' and $current <= 'z') or ($current >= 'A' and $current <= 'Z'))
				$letter[] = $current;
			if($current >= '0' and $current <= '9')
				$number[] = $current;
		}
		
		if(strlen($password) > 0)
		{
			if(strlen($password) < $min_length)
				$form->get("password")->addError(new FormError($translator->trans('user.createAccount.PasswordMustContainAtLeast', ["%minLength%" => $min_length])));
			else
			{
				if(count($letter) == 0)
					$form->get('password')->addError(new FormError($translator->trans('user.createAccount.PasswordOneLetter')));
				if(count($number) == 0)
					$form->get('password')->addError(new FormError($translator->trans('user.createAccount.PasswordOneNumber')));
			}
		}
	}
	
	// Profil show
	public function quotesUserDatatablesAction(Request $request, TokenStorageInterface $tokenStorage, TranslatorInterface $translator, AuthorizationCheckerInterface $authChecker, $username)
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

		$entities = $entityManager->getRepository(Quote::class)->findQuoteByUserAndAuhorType($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $tokenStorage->getToken()->getUser(), 'user');
		$iTotal = $entityManager->getRepository(Quote::class)->findQuoteByUserAndAuhorType($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $tokenStorage->getToken()->getUser(), 'user', true);

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

			if ($authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') and $tokenStorage->getToken()->getUser()->getUsername() == $username) {
				$row[] = '<div class="state_entity '.$entity->getStateRealName().'">'.$translator->trans($entity->getStateString()).'</div>';
				$row[] = '<a href="'.$this->generateUrl('quoteuser_edit', array("id" => $entity->getId())).'" alt=""><span class="fas fa-pencil">'.$translator->trans('user.myProfile.Edit').'</span></a> / <a href="#" alt="" data-id="'.$entity->getId().'" class="delete_poem"><span class="fas fa-times">'.$translator->trans('user.myProfile.Delete').'</span></a>';
			}
			
			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	//** Mes Votes
	public function votesUserDatatablesAction(Request $request, $username)
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
		$entities = $entityManager->getRepository(Vote::class)->findVoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username);
		$iTotal = $entityManager->getRepository(Vote::class)->findVoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $this->generateUrl('read', array('id' => $entity['id'], 'slug' => $entity["slug"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['text'].'</a>';
			
			list($icon, $color) = (($entity['vote'] == -1) ? array("fa-arrow-down", "red") : array("fa-arrow-up", "green"));
			$row[] = "<i class='fab ".$icon."' aria-hidden='true' style='color: ".$color.";'></i>";

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	//** Mes Commentaires
	public function commentsUserDatatablesAction(Request $request, $username)
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
		$entities = $entityManager->getRepository(Comment::class)->findCommentByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username);
		$iTotal = $entityManager->getRepository(Comment::class)->findCommentByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			$row = array();

			$show = $this->generateUrl('read', array('id' => $entity['id'], 'slug' => $entity["slug"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity['text'].'</a>';
			$row[] = "le ".$entity['created_at']->format("d/m/Y à H:i:s");

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
}