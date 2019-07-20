<?php

namespace App\Repository;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use App\Entity\Quote;

/**
 * Quote repository
 */
class QuoteRepository extends ServiceEntityRepository implements iRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Quote::class);
    }

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->createQueryBuilder("pf");

		$aColumns = array( 'pf.id', 'pf.text', 'pf.id');

		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pf.text LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(pf) AS count");
			return $qb->getQuery()->getSingleScalarResult();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
	}

	public function getRandom($locale)
	{
		$qb = $this->createQueryBuilder("pt");

		$qb->select("COUNT(pt) AS countRow");
		
		$this->whereLanguage($qb, "pt", $locale);
		
		$max = max($qb->getQuery()->getSingleScalarResult() - 1, 0);
		$offset = rand(0, $max);

		$qb = $this->createQueryBuilder("pt");

		$qb->setFirstResult($offset)
		   ->setMaxResults(1);
		 
		$this->whereLanguage($qb, "pt", $locale);

		return $qb->getQuery()->getOneOrNullResult();
	}

	public function getLastEntries($locale)
	{
		$qb = $this->createQueryBuilder("pt");

		$qb->setMaxResults(7)
		   ->orderBy("pt.id", "DESC");
		   
		$this->whereLanguage($qb, "pt", $locale, true);
		   
		return $qb->getQuery()->getResult();
	}
	
	public function getStat($locale)
	{
		$qb = $this->createQueryBuilder("pt");
		
		$this->whereLanguage($qb, "pt", $locale);

		$qb->select("COUNT(pt)");
		
		return $qb->getQuery()->getSingleScalarResult();
	}

	public function checkForDoubloon($entity)
	{
		$qb = $this->createQueryBuilder("pf");

		$qb->select("COUNT(pf) AS count")
		   ->where("pf.slug = :slug")
		   ->setParameter('slug', $entity->getSlug());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}

		return $qb->getQuery()->getSingleScalarResult();
	}

	public function browsingShow($id)
	{
		// Previous
		$subqueryPrevious = 'p.id = (SELECT MAX(p2.id) FROM App\Entity\Quote p2 WHERE p2.id < '.$id.')';
		$qb_previous = $this->createQueryBuilder('p');
		
		$qb_previous->select("p.id, p.text, p.slug AS slug")
		   ->andWhere($subqueryPrevious);
		   
		// Next
		$subqueryNext = 'p.id = (SELECT MIN(p2.id) FROM App\Entity\Quote p2 WHERE p2.id > '.$id.')';
		$qb_next = $this->createQueryBuilder('p');
		
		$qb_next->select("p.id, p.text, p.slug AS slug")
		   ->andWhere($subqueryNext);
		
		$res = array(
			"previous" => $qb_previous->getQuery()->getOneOrNullResult(),
			"next" => $qb_next->getQuery()->getOneOrNullResult()
		);

		return $res;
	}

	public function whereLanguage($qb, $alias, $locale, $join = true)
	{
		if($join)
			$qb->leftjoin($alias.".language", "la");
		
		$qb->andWhere('la.abbreviation = :locale')
		   ->setParameter("locale", $locale);
		
		return $qb;
	}
}