<?php

namespace App\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\FileManagement;

class FileManagementTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($entity)
    {
		// die(var_dump($entity));;
        if (null === $entity) {
            return null;
        }

        return ["id" => $entity->getId(), "filename" => $entity->getPhoto()];
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $issueNumber
     * @return Issue|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($entityNumber)
    {
        // no issue number? It's optional, so that's ok
        if (!$entityNumber or (!isset($entityNumber["id"]) or empty($entityNumber["id"]))) {
            return;
        }

        $entity = $this->entityManager
            ->getRepository(FileManagement::class)
            // query for the issue with this id
            ->find($entityNumber["id"])
        ;

        if (null === $entity) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $entityNumber["id"]
            ));
        }

        return $entity;
    }
}