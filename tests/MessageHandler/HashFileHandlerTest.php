<?php

namespace App\Tests\MessageHandler;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Entity\File;
use App\Entity\Fileset;
use App\Entity\Person;
use App\Message\HashFile;

class HashFileHandlerTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $messengeBus;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
            
        $this->messengeBus = $kernel->getContainer()
            ->get('message_bus');
    }
    
    public function testHash()
    {
        $testFilePath = __DIR__.'/../fixtures/hashMe.txt';
        $systemPerson = $this->entityManager->getRepository(Person::class)->find(0);
        
        $fileset = new Fileset();

        $newFile = new File();
        $newFile->setFileName('testName');
        $newFile->setFileSize('12345');
        $newFile->setUploadedAt(new \DateTime('now'));
        $newFile->setUploadedBy($systemPerson);
        $newFile->setFilePath($testFilePath);
        $fileset->addFile($newFile);
       
        $this->entityManager->persist($fileset);
        $this->entityManager->flush();
 
        $fileId = $fileset->getFiles()->first()->getId();
        
        $this->messengeBus->dispatch(new HashFile($fileId));
        
        $fileHandlerHash = $newFile->getFileSha256Hash();
        $fileHash = hash_file('sha256', $testFilePath);

        $this->assertSame($fileHash, $fileHandlerHash);
    }
}