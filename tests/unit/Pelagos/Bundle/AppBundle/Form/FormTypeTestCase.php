<?php

namespace Tests\unit\Pelagos\Bundle\AppBundle\Form;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Test case used to test form types with entity fields.
 */
abstract class FormTypeTestCase extends TypeTestCase
{
    /**
     * A test entity manager.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * A mock entity manager registry.
     *
     * @var ManagerRegistry
     */
    protected $entityManagerRegistry;

    /**
     * The form under test.
     *
     * @var FormInterface
     */
    protected $form;

    /**
     * A property accessor.
     *
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * Set up for each test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->entityManager = DoctrineTestHelper::createTestEntityManager();
        $this->entityManagerRegistry = $this->createRegistryMock('default', $this->entityManager);

        $config = $this->entityManager->getConfiguration();
        $driverImpl = $config->newDefaultAnnotationDriver(array('src/Pelagos/Entity'), false);
        $config->setMetadataDriverImpl($driverImpl);

        $this->createSchema();

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(
                new FormTypeValidatorExtension(
                    $validator
                )
            )
            ->addTypeGuesser(
                $this->getMockBuilder(ValidatorTypeGuesser::class)
                     ->disableOriginalConstructor()
                     ->getMock()
            )
            ->getFormFactory();

        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Create the schema that will be used for testing.
     *
     * @return void
     */
    protected function createSchema()
    {
        if (!Type::hasType('geometry')) {
            // Register geometry as a text type for testing.
            Type::addType('geometry', TextType::class);
        }

        if (!Type::hasType('citext')) {
            // Register citext as a text type for testing.
            Type::addType('citext', TextType::class);
        }

        $schemaTool = new SchemaTool($this->entityManager);

        $classes = array();

        foreach ($this->getEntities() as $entityClass) {
            $classes[] = $this->entityManager->getClassMetadata($entityClass);
        }

        // Drop and recreate the schema.
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);
    }

    /**
     * Get form extensions to load.
     *
     * @return array
     */
    protected function getExtensions()
    {
        return array_merge(parent::getExtensions(), array(
            new DoctrineOrmExtension($this->entityManagerRegistry),
        ));
    }

    /**
     * Return the entities to map with the test entity manager.
     *
     * @return array
     */
    protected function getEntities()
    {
        return array();
    }

    /**
     * Create a mock of entity manager registry.
     *
     * @param string        $name          Name of mock entity manager registry.
     * @param EntityManager $entityManager Entity manager to return.
     *
     * @return ManagerRegistry
     */
    protected function createRegistryMock($name, EntityManager $entityManager)
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
                 ->method('getManager')
                 ->with($this->equalTo($name))
                 ->will($this->returnValue($entityManager));

        $registry->expects($this->any())
                 ->method('getManagerForClass')
                 ->will($this->returnValue($entityManager));

        return $registry;
    }
}
