<?php

namespace App\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Source;

/**
 * Source repository
 */
class SourceRepository extends ServiceEntityRepository implements iRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Source::class);
    }
	
	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $state = "all", $count = false)
	{
		$qb = $this->createQueryBuilder("pf");

		$aColumns = array( 'pf.id', 'pf.title', 'la.title', 'pf.id');
		
		$qb->leftjoin("pf.language", "la");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pf.title LIKE :search')
			   ->setParameter('search', $search);
		}

		if($state == "toComplete")
		{
			$qb->andWhere('pf.text IS NULL OR pf.photo IS NULL');
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
	
	public function getSourceByAuthorsAndTitle($author, string $title)
	{
		$qb = $this->createQueryBuilder("pf");
	
		$qb->leftjoin("pf.authors", "author")
		   ->where("author.id = :author")
		   ->setParameter("author", $author)
		   ->andWhere("pf.title = :title")
		   ->setParameter("title", $title)
		   ->setMaxResults(1);
		   
		return $qb->getQuery()->getOneOrNullResult();
	}
	
	public function findAllForChoice($locale)
	{
		$qb = $this->createQueryBuilder("pf");
		
		$qb->select("pf.id AS id, pf.title AS title")
		   ->leftjoin("pf.language", "la")
		   ->where('la.abbreviation = :locale')
		   ->setParameter('locale', $locale)
		   ->orderBy("title", "ASC");

		return $qb;
	}
	
	public function findAllFictionalCharactersForChoice($locale)
	{
		$qb = $this->createQueryBuilder("pf");
		
		$qb
		   ->leftjoin("pf.language", "la")
		   ->where('la.abbreviation = :locale')
		   ->setParameter('locale', $locale)
		   ->andWhere("pf.type = :type")
		   ->setParameter("type", Biography::FICTIONAL_CHARACTER)
		   ->orderBy("pf.title", "ASC");

		return $qb;
	}
	
	public function checkForDoubloon($entity)
	{
		$qb = $this->createQueryBuilder("pf");

		$qb->select("COUNT(pf) AS number")
		   ->leftjoin("pf.language", "la")
		   ->where("pf.slug = :slug")
		   ->setParameter('slug', $entity->getSlug())
		   ->andWhere("la.id = :idLanguage")
		   ->setParameter("idLanguage", $entity->getLanguage())
		   ->leftjoin("pf.authors", "a")
		   ->andWhere("a.id IN (:authors)")
		   ->setParameter("authors", $entity->getAuthors());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}

		return $qb->getQuery()->getSingleScalarResult();
	}
	
	public function getDatasSelect($locale, $query)
	{
		$qb = $this->createQueryBuilder("pf");
		
		$qb
		   ->leftjoin("pf.language", "la")
		   ->where('la.id = :locale')
		   ->setParameter('locale', $locale)
		   ->orderBy("pf.title", "ASC")
		   ->setMaxResults(15);
		   
		if(!empty($query))
		{
			$query = "%".$query."%";
			$qb->andWhere("pf.title LIKE :query")
			   ->setParameter("query", $query);
		}

		return $qb->getQuery()->getResult();
	}
}