<?php

namespace App\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\Biography;

class BiographyTransformer implements DataTransformerInterface
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
    public function transform($biography)
    {
        if (null === $biography) {
            return '';
        }

        return $biography->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $issueNumber
     * @return Issue|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($biographyNumber)
    {
        // no issue number? It's optional, so that's ok
        if (!$biographyNumber) {
            return;
        }

        $biography = $this->entityManager
            ->getRepository(Biography::class)
            // query for the issue with this id
            ->find($biographyNumber)
        ;

        if (null === $biography) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $biographyNumber
            ));
        }

        return $biography;
    }
}