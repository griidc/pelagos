<?php

namespace App\Util;

use App\Entity\ResearchGroup;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;

class CustomResearchGroupGenerator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, $entity): mixed
    {
        // Generate a unique ID for the Research Group
        $nextId = $em->getRepository(ResearchGroup::class)->getNextAvailableId();

        return $nextId;
    }
}
