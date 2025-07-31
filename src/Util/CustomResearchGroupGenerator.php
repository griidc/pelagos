<?php

namespace App\Util;

use App\Entity\ResearchGroup;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use InvalidArgumentException;

class CustomResearchGroupGenerator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, $entity): mixed
    {
        // Validate that $entity is an instance of ResearchGroup
        if (!$entity instanceof ResearchGroup) {
            throw new \InvalidArgumentException('Expected $entity to be an instance of ResearchGroup.');
        }
        // Generate a unique ID for the Research Group
        $nextId = $em->getRepository(ResearchGroup::class)->getNextAvailableId();

        return $nextId;
    }
}
