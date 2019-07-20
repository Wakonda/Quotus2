<?php

namespace App\Repository;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use App\Entity\Comment;

/**
 * Comment repository
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

	public function countAllComments($id)
	{
		$qb = $this->createQueryBuilder('c');
		
		$qb->select("COUNT(c) AS count")
		   ->leftjoin('c.quote', 'p')
		   ->where('p.id = :id')
		   ->setParameter('id', $id);

		return $qb->getQuery()->getSingleScalarResult();
	}
	
	public function displayComments($id, $max_comment_by_page, $first_message_to_display)
	{
		$qb = $this->createQueryBuilder("c");
		
		$first_message_to_display = ($first_message_to_display < 0) ? 0 : $first_message_to_display;

		$qb->leftjoin('c.quote', 'p')
		   ->where('p.id = :id')
		   ->setParameter('id', $id)
		   ->setMaxResults($max_comment_by_page)
		   ->setFirstResult($first_message_to_display)
		   ->orderBy("c.created_at", "DESC");

		return $qb->getQuery()->getResult();
	}

	public function findCommentByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $count = false)
	{
		$qb = $this->createQueryBuilder("co");

		$aColumns = array('pf.text', 'co.created_at');
		
		$qb->select("pf.id, pf.text, co.created_at, pf.slug AS slug")
		   ->leftjoin("co.user", "bp")
		   ->leftjoin("co.quote", "pf")
		   ->where("bp.username = :username")
		   ->setParameter("username", $username)
		   ->orderBy("co.created_at", "DESC");
		   
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andhere('pf.title LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(co) AS count");
			return $qb->getQuery()->getSingleScalarResult();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
	}
}