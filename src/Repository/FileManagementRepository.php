<?php

namespace App\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\FileManagement;

/**
 * FileManagement repository
 */
class FileManagementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileManagement::class);
    }
	
	public function loadAjax($folder, $page, $iDisplayLength)
	{
		$iDisplayStart = ($page - 1) * $iDisplayLength;
		
		$qb = $this->createQueryBuilder("p");
		
		$qb->andWhere("p.folder = :folder")
		   ->setParameter("folder", $folder)
		   ->setFirstResult($iDisplayStart)
		   ->setMaxResults($iDisplayLength);
		
		return $qb->getQuery()->getResult();
	}
}