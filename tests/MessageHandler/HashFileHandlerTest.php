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

        $file = new File();
        $file->setFileName('testName');
        $file->setFileSize('12345');
        $file->setUploadedAt(new \DateTime('now'));
        $file->setUploadedBy($systemPerson);
        $file->setFilePath($testFilePath);
        $fileset->addFile($file);
       
        $this->entityManager->persist($fileset);
 
        $fileId = $file->getId() ?? 0;
        
        $this->messengeBus->dispatch(new HashFile($fileId));
        
        $fileHandlerHash = $file->getFileSha256Hash();
        $fileHash = hash_file('sha256', $testFilePath);

        $this->assertSame($fileHash, $fileHandlerHash);
    }
}