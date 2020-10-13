<?php

namespace App\Event;

use App\Entity\File;

use Doctrine\ORM\Event\LifecycleEventArgs;

class FileProcessListener
{
    /**
     * Post update method for dataset entity, to publish/update Doi.
     *
     * @param File               $file An instance of File entity.
     * @param LifecycleEventArgs $args Doctrine life cycle event args.
     *
     * @return void
     */
    public function preUpdate(File $file, LifecycleEventArgs $args): void
    {
        $file = $args->getObject();
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $metadata = $entityManager->getClassMetadata(get_class($file));
        if ($file instanceof File) {
            if ($file->getFileSha256Hash()) {
                $file->setStatus(File::FILE_DONE);
                $unitOfWork->recomputeSingleEntityChangeSet($metadata, $file);
            }
        }
    }
}
