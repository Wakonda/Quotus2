<?php

namespace App\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use App\Entity\User;

/**
 * User repository
 */
class UserRepository extends ServiceEntityRepository implements iRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

	public function findAllForChoice()
	{
		$qb = $this->createQueryBuilder("pf");
		
		$qb->orderBy("username", "ASC");

		return $qb;
	}
	
	public function findByUsernameOrEmail($field)
	{
		$qb = $this->createQueryBuilder("u");
		
		$qb->where("u.username = :field")
		   ->orWhere("u.email = :field")
		   ->setParameter("field", $field)
		   ->setMaxResults(1);

		return $qb->getQuery()->getOneOrNullResult();
	}
	
	public function checkForDoubloon($entity)
	{
		$qb = $this->createQueryBuilder("pf");

		$qb->select("COUNT(pf) AS count")
		   ->where("pf.username = :username")
		   ->orWhere("pf.email = :email")
		   ->setParameter('username', $entity->getUsername())
		   ->setParameter('email', $entity->getEmail());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		
		return $qb->getQuery()->getSingleScalarResult();
	}

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->createQueryBuilder("u");

		$aColumns = array( 'u.id', 'u.username', 'u.id');
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('u.username LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(u) AS count");
			return $qb->getQuery()->getSingleScalarResult();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
	}
}