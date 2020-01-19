<?php

namespace App\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use App\Entity\Quote;

/**
 * Quote repository
 */
class QuoteRepository extends ServiceEntityRepository implements iRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->createQueryBuilder("pa");

		$aColumns = array( 'pa.id', 'pa.text', 'pa.id');

		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pa.text LIKE :search')
			   ->setParameter('search', $search)
			   ->andWhere("pa.state = :state")
		       ->setParameter("state", Quote::PUBLISHED_STATE);
		}
		if($count)
		{
			$qb->select("COUNT(pa) AS count");
			return $qb->getQuery()->getSingleScalarResult();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
	}

	public function findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $datasObject, $locale, $count = false)
	{
		$aColumns = array( 'pf.text', 'bi.title');
		$qb = $this->createQueryBuilder("pf");
		
		$qb
		   ->andWhere("pf.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE)
		   ->leftjoin("pf.biography", "bi");

		$this->whereLanguage($qb, 'pf', $locale);
		
		if(!empty($datasObject->source))
		{
			$qb->leftjoin("pf.source", "so")
			   ->andWhere("so.title LIKE :title")
			   ->setParameter("title", "%".$datasObject->source."%");
		}
		
		if(!empty($datasObject->biography))
		{
			$qb->andWhere("bi.title LIKE :biography")
			   ->setParameter("biography", "%".$datasObject->biography."%");
		}

		if(!empty($datasObject->type))
		{
			$qb->andWhere("bi.type = :type")
			   ->setParameter("type", $datasObject->type);
		}

		if(!empty($datasObject->text))
		{
			$keywords = explode(",", $datasObject->text);
			$i = 0;
			foreach($keywords as $keyword)
			{
				$keyword = "%".$keyword."%";
				$qb->andWhere("pf.text LIKE :keyword".$i)
			       ->setParameter("keyword".$i, $keyword);
				$i++;
			}
		}

		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
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
		$qb = $this->createQueryBuilder("pa");

		$qb->select("COUNT(pa) AS countRow");
		
		$this->whereLanguage($qb, "pa", $locale);
		
		$max = max($qb->getQuery()->getSingleScalarResult() - 1, 0);
		$offset = rand(0, $max);

		$qb = $this->createQueryBuilder("pa");

		$qb->setFirstResult($offset)
		   ->setMaxResults(1)
		   ->andWhere("pa.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE);
		 
		$this->whereLanguage($qb, "pa", $locale);

		return $qb->getQuery()->getOneOrNullResult();
	}

	public function getLastEntries($locale)
	{
		$qb = $this->createQueryBuilder("pa");

		$qb->where("pa.authorType = :biography")
		   ->setParameter("biography", Quote::BIOGRAPHY_AUTHORTYPE)
		   ->setMaxResults(7)
		   ->orderBy("pa.id", "DESC")
		   ->andWhere("pa.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE);
		   
		$this->whereLanguage($qb, "pa", $locale, true);
		   
		return $qb->getQuery()->getResult();
	}
	
	public function getStat($locale)
	{
		$qb = $this->createQueryBuilder("pa");
		
		$this->whereLanguage($qb, "pa", $locale);

		$qb->select("COUNT(pa)")
		   ->andWhere("pa.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE);
		
		return $qb->getQuery()->getSingleScalarResult();
	}

	public function checkForDoubloon($entity)
	{
		$qb = $this->createQueryBuilder("pa");

		$qb->select("COUNT(pa) AS count")
		   ->where("pa.slug = :slug")
		   ->setParameter('slug', $entity->getSlug());

		if($entity->isBiography())
		{
			$qb->andWhere("pa.biography = :biographyId")
			   ->setParameter("biographyId", $entity->getBiography()->getId());
		}

		if($entity->isUser())
		{
			$qb->andWhere("pa.user = :userId")
			   ->setParameter("userId", $entity->getUser()->getId());
		}

		if($entity->getId() != null)
		{
			$qb->andWhere("pa.id != :id")
			   ->setParameter("id", $entity->getId());
		}

		return $qb->getQuery()->getSingleScalarResult();
	}

	public function getEntityByTagDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $tagId, $count = false)
	{
		$qb = $this->createQueryBuilder("pf");

		$aColumns = array( 'pf.text', 'bi.title');
		
		$qb
		   ->where("bo.id = :id")
		   ->andWhere("pf.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE)
		   ->leftjoin("pf.biography", "bi")
		   ->leftjoin("pf.tags", "bo")
		   ->setParameter("id", $tagId);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(DISTINCT pf.id) AS count");
			return $qb->getQuery()->getSingleScalarResult();
		}
		else
		{
			$qb->groupBy("pf.id")
			   ->setFirstResult($iDisplayStart)
			   ->setMaxResults($iDisplayLength);
		}

		return $qb->getQuery()->getResult();
	}

	public function browsingShow($id)
	{
		// Previous
		$subqueryPrevious = 'p.id = (SELECT MAX(p2.id) FROM App\Entity\Quote p2 WHERE p2.id < '.$id.' AND p2.state = '.Quote::PUBLISHED_STATE.')';
		$qb_previous = $this->createQueryBuilder('p');
		
		$qb_previous->select("p.id, p.text, p.slug AS slug")
		   ->andWhere($subqueryPrevious);
		   
		// Next
		$subqueryNext = 'p.id = (SELECT MIN(p2.id) FROM App\Entity\Quote p2 WHERE p2.id > '.$id.' AND p2.state = '.Quote::PUBLISHED_STATE.')';
		$qb_next = $this->createQueryBuilder('p');
		
		$qb_next->select("p.id, p.text, p.slug AS slug")
		   ->andWhere($subqueryNext);
		
		$res = array(
			"previous" => $qb_previous->getQuery()->getOneOrNullResult(),
			"next" => $qb_next->getQuery()->getOneOrNullResult()
		);

		return $res;
	}

    public function findQuoteBySource($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locale, $count = false)
    {
		$qb = $this->createQueryBuilder("pa");

		$aColumns = array( 'co.title', 'COUNT(pa.id)');
		
		$qb->select("co.id AS source_id, co.title AS source_title, COUNT(pa.id) AS number_by_source, co.slug AS source_slug, co.photo AS source_photo")
		   ->join("pa.source", "co")
		   ->groupBy("co.id, co.title")
		   ->andWhere("pa.authorType = :biography")
		   ->setParameter("biography", Quote::BIOGRAPHY_AUTHORTYPE)
		   ->andWhere("pa.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE)
		   ;
		
		$this->whereLanguage($qb, 'pa', $locale);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('co.title LIKE :search')
               ->setParameter("search", $search);
		}
		if($count)
		{
			$params = [];
			
			foreach($qb->getParameters()->getIterator() as $i => $item)
				$params[] = $item->getValue();

			$res = $this->_em->getConnection()->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getQuery()->getSql().") AS SQ", $params);

			return $res->fetch()["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
    }

	public function getQuoteBySourceDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $sourceId, $count = false)
	{
		$qb = $this->createQueryBuilder("pa");

		$aColumns = array('pa.text', 'bo.title', 'pa.id');
		
		$qb->select("pa.text AS quote_text, pa.id AS quote_id, pa.slug AS quote_slug, bo.title AS quote_author")
		   ->leftjoin("pa.source", "co")
		   ->leftjoin("pa.biography", "bo")
		   ->where("co.id = :id")
		   ->setParameter("id", $sourceId)
		   ->andWhere("pa.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE)
		   ->andWhere("pa.authorType = :biography")
		   ->setParameter("biography", Quote::BIOGRAPHY_AUTHORTYPE);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pa.text LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(pa) AS count");
			return $qb->getQuery()->getSingleScalarResult();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
	}

    public function findQuoteByBiography($type, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locale, $count = false)
    {
		$qb = $this->createQueryBuilder("pa");

		$aColumns = array( 'co.title', 'COUNT(pa.id)');
		
		$qb->select("co.id AS biography_id, co.title AS biography_title, COUNT(pa.id) AS number_by_biography, co.slug AS biography_slug, co.photo AS biography_photo")
		   ->leftjoin("pa.biography", "co")
		   ->groupBy("co.id, co.title")
		   ->andWhere("co.type = :type")
		   ->setParameter("type", $type)
		   ->andWhere("pa.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE)
		   ->andWhere("pa.authorType = :biography")
		   ->setParameter("biography", Quote::BIOGRAPHY_AUTHORTYPE)
		   ;
		
		$this->whereLanguage($qb, 'pa', $locale);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('co.title LIKE :search')
               ->setParameter("search", $search);
		}
		if($count)
		{
			$params = [];
			
			foreach($qb->getParameters()->getIterator() as $i => $item)
				$params[] = $item->getValue();

			$res = $this->_em->getConnection()->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getQuery()->getSql().") AS SQ", $params);

			return $res->fetch()["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
    }

	public function getQuoteByBiographyDatatables($type, $iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $biographyId, $count = false)
	{
		$qb = $this->createQueryBuilder("pa");

		$aColumns = array('pa.text', 'pa.id');
		
		$qb->select("pa.text AS quote_text, so.id AS source_id, so.slug AS source_slug, so.title AS source_text, pa.id AS quote_id, pa.slug AS quote_slug")
		   ->leftjoin("pa.biography", "co")
		   ->leftjoin("pa.source", "so")
		   ->where("co.id = :id")
		   ->setParameter("id", $biographyId)
		   ->andWhere("co.type = :type")
		   ->setParameter("type", $type)
		   ->andWhere("pa.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE)
		   ->andWhere("pa.authorType = :biography")
		   ->setParameter("biography", Quote::BIOGRAPHY_AUTHORTYPE);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pa.text LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(pa) AS count");
			return $qb->getQuery()->getSingleScalarResult();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
	}

    public function findQuoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locale, $count = false)
    {
		$qb = $this->createQueryBuilder("pa");

		$aColumns = array( 'co.username', 'COUNT(pa.id)');
		
		$qb->select("pa.id AS id, pa.text AS text, pa.slug AS slug, co.username AS username")
		   ->leftjoin("pa.user", "co")
		   ->andWhere("pa.state = :state")
		   ->setParameter("state", Quote::PUBLISHED_STATE)
		   ->andWhere("pa.authorType = :biography")
		   ->setParameter("biography", Quote::USER_AUTHORTYPE)
		   ;
		
		$this->whereLanguage($qb, 'pa', $locale);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('co.username LIKE :search')
               ->setParameter("search", $search);
		}
		if($count)
		{
			$params = [];
			
			foreach($qb->getParameters()->getIterator() as $i => $item)
				$params[] = $item->getValue();

			$res = $this->_em->getConnection()->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getQuery()->getSql().") AS SQ", $params);

			return $res->fetch()["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
    }

	public function whereLanguage($qb, $alias, $locale, $join = true)
	{
		if($join)
			$qb->leftjoin($alias.".language", "la");
		
		$qb->andWhere('la.abbreviation = :locale')
		   ->setParameter("locale", $locale);
		
		return $qb;
	}

	public function findQuoteByUserAndAuhorType($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $currentUser, $authorType, $count = false)
	{
		$qb = $this->createQueryBuilder("pf");

		$aColumns = array( 'pf.id', 'pf.text', 'pf.state', 'pf.id');
		
		$qb->leftjoin("pf.user", "pfu")
		   ->where("pfu.username = :username")
		   ->setParameter("username", $username)
		   ->andWhere("pf.state <> 2")
		   ->andWhere('pf.authorType = :authorType')
		   ->setParameter('authorType', $authorType);

		if($username != $currentUser->getUsername())
		{
			$qb->andWhere("pf.state = 0");
		}
		
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
			$qb->select("COUNT(pf) AS count");
			return $qb->getQuery()->getSingleScalarResult();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
	}
}