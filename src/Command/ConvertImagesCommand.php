<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Source;
use App\Entity\Biography;
use App\Entity\Tag;
use App\Entity\FileManagement;

class ConvertImagesCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:convert-image';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
		parent::__construct();
        $this->em = $em;
    }
	
    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		// Source
		$entities = $this->em->getRepository(Source::class)->findAll();
		
		foreach($entities as $entity) {
			if(empty($entity->getFileManagement())) {
				if(empty($entity->getPhoto()))
					continue;

				$fm = new FileManagement();
				
				$fm->setPhoto($entity->getPhoto());
				$fm->setFolder("source");
				
				$entity->setFileManagement($fm);
			
				$this->em->persist($fm);
				$this->em->persist($entity);
			}
		}
		
		$this->em->flush();

		// Biography
		$entities = $this->em->getRepository(Biography::class)->findAll();
		
		foreach($entities as $entity) {
			if(empty($entity->getFileManagement())) {
				if(empty($entity->getPhoto()))
					continue;

				$fm = new FileManagement();
				
				$fm->setPhoto($entity->getPhoto());
				$fm->setFolder("biography");
				
				$entity->setFileManagement($fm);
			
				$this->em->persist($fm);
				$this->em->persist($entity);
			}
		}
		
		$this->em->flush();

		// Tag
		$entities = $this->em->getRepository(Tag::class)->findAll();
		
		foreach($entities as $entity) {
			if(empty($entity->getFileManagement())) {
				if(empty($entity->getPhoto()))
					continue;

				$fm = new FileManagement();
				
				$fm->setPhoto($entity->getPhoto());
				
				$entity->setFileManagement($fm);
				$fm->setFolder("tag");
			
				$this->em->persist($fm);
				$this->em->persist($entity);
			}
		}
		
		$this->em->flush();

		return 0;
    }
}