<?php

namespace App\Repository;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use App\Entity\QuoteImage;

/**
 * QuoteImage repository
 */
class QuoteImageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, QuoteImage::class);
    }

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->createQueryBuilder("ip");

		$aColumns = array( 'pf.text', null, 'pf.id');

		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
	   
	    $qb->join('ip.quote', 'pf');
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pf.text LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(ip) AS count");
			return $qb->getQuery()->getSingleScalarResult();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		return $qb->getQuery()->getResult();
	}

	public function getPaginator($locale)
	{
		$qb = $this->createQueryBuilder("ip");

		$qb->join('ip.quote', 'pf')
		   ->join('pf.language', 'la')
		   ->where('la.abbreviation = :locale')
		   ->setParameter("locale", $locale)
		   ->orderBy("ip.id", "DESC");

		return $qb->getQuery();
	}
}