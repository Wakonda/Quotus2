<?php

namespace App\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Biography;

/**
 * Biography repository
 */
class BiographyRepository extends ServiceEntityRepository implements iRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Biography::class);
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
	
	public function findAllForChoice($locale, $type)
	{
		$qb = $this->createQueryBuilder("pf");
		
		$qb
		   ->leftjoin("pf.language", "la")
		   ->where('la.abbreviation = :locale')
		   ->setParameter('locale', $locale)
		   ->andWhere("pf.type = :type")
		   ->setParameter("type", $type)
		   ->orderBy("pf.title", "ASC");

		return $qb;
	}
	
	public function getDatasSelect($type, $locale, $query, $source, $count = false)
	{
		$qb = $this->createQueryBuilder("pf");
		
		if(!empty($locale))
		{
			$qb
			   ->leftjoin("pf.language", "la")
			   ->where('la.id = :locale')
			   ->setParameter('locale', $locale);
		}

		if(!empty($type))
		{
		   $qb->andWhere("pf.type = :type")
		      ->setParameter("type", $type);
			
		}

		if(!empty($source))
		{
		   $qb->leftjoin("pf.sources", "srcs")
		      ->leftjoin("pf.artworks", "atws")
		      ->andWhere("srcs.id = :source OR atws.id = :source")
		      ->setParameter("source", $source);
			
		}
		   
		if(!empty($query))
		{
			$query = is_array($query) ? "%".$query[0]."%" : "%".$query."%";
			$query = "%".$query."%";
			$qb->andWhere("pf.title LIKE :query")
			   ->setParameter("query", $query);
		}
		
		if($count)
			return $qb->select("COUNT(pf)")->getQuery()->getSingleScalarResult();
		
		$qb->orderBy("pf.title", "ASC")
		   ->setMaxResults(15);

		return $qb->getQuery()->getResult();
	}
	
	public function checkForDoubloon($entity)
	{
		$qb = $this->createQueryBuilder("pf");

		$qb->select("COUNT(pf) AS number")
		   ->leftjoin("pf.language", "la")
		   ->where("pf.slug = :slug")
		   ->setParameter('slug', $entity->getSlug())
		   ->andWhere("la.id = :idLanguage")
		   ->setParameter("idLanguage", $entity->getLanguage());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		return $qb->getQuery()->getSingleScalarResult();
	}

	// Combobox
	public function getDatasCombobox($params, $locale, $count = false)
	{
		$qb = $this->createQueryBuilder("b");

		if(!empty($locale))
			$qb->leftjoin("b.language", "l")
			   ->andWhere("l.id = :localeId")
			   ->setParameter("localeId", $locale);
		
		if(array_key_exists("pkey_val", $params))
		{
			$qb->select("b.id, b.title")
			   ->andWhere('b.id = :id')
			   ->setParameter('id', $params['pkey_val']);
			   
			return $qb->getQuery()->getOneOrNullResult();
		}
		
		$params['offset']  = ($params['page_num'] - 1) * $params['per_page'];

		$qb->select("b.id, b.title")
		   ->andWhere("b.title LIKE :title")
		   ->setParameter("title", "%".implode(' ', $params['q_word'])."%")
		   ->setMaxResults($params['per_page'])
		   ->setFirstResult($params['offset'])
		   ;
		
		if($count)
		{
			$qb->select("COUNT(b.id)")
			   ->andWhere("b.title LIKE :title")
			   ->setParameter("title", "%".implode(' ', $params['q_word'])."%")
			   ;
			   
			return $qb->getQuery()->getSingleScalarResult();
		}

		return $qb->getQuery()->getResult();
	}
}