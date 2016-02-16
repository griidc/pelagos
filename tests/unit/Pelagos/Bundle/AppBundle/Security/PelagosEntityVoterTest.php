<?php

namespace Tests\unit\Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Doctrine\Common\Collections\ArrayCollection;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles as DR_Roles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\FundingOrganizationRoles as FO_Roles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles as RG_Roles;

use Pelagos\Bundle\AppBundle\Security\PelagosEntityVoter as Voter;

use Pelagos\Entity\PersonAssociationInterface;
use Pelagos\Entity\Entity;

/**
 * Abstract class containing utility methods and tests shared by all Pelagos Entity voter tests.
 */
abstract class PelagosEntityVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * An instance of the voter under test.
     *
     * @var Voter
     */
    protected $voter;

    /**
     * Roles and the permissions they should have.
     *
     * Default is to abstain for all roles.
     *
     * @var array
     */
    protected $roles = array(
        'DataRepository' => array(
            DR_Roles::MANAGER => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
            DR_Roles::ENGINEER => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
            DR_Roles::SUPPORT => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
            DR_Roles::SME => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
        ),
        'FundingOrganization' => array(
            FO_Roles::LEADERSHIP => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
            FO_Roles::ADVISORY => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
            FO_Roles::ADMIN => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
        ),
        'ResearchGroup' => array(
            RG_Roles::LEADERSHIP => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
            RG_Roles::ADMIN => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
            RG_Roles::DATA => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
            RG_Roles::RESEARCHER => array(
                Voter::CAN_CREATE => Voter::ACCESS_ABSTAIN,
                Voter::CAN_EDIT => Voter::ACCESS_ABSTAIN,
                Voter::CAN_DELETE => Voter::ACCESS_ABSTAIN,
            ),
        ),
    );

    /**
     * An array of mock tokens for Persons with various roles.
     *
     * @var array
     */
    protected $mockTokens = array();

    /**
     * An array of person associations.
     *
     * @var array
     */
    protected $personAssociations = array();

    /**
     * Property to hold a mock Entity for testing.
     *
     * @var Entity
     */
    protected $mockEntity;

    /**
     * Set up run for each test.
     *
     * @return void
     */
    public function setUp()
    {
        foreach ($this->roles as $type => $roleNames) {
            $this->mockTokens[$type] = array();
            $this->personAssociations[$type] = array();
            foreach (array_keys($roleNames) as $roleName) {
                $this->mockTokens[$type][$roleName] = $this->createMockToken();
                $this->personAssociations[$type][] = $this->createMockPersonAssociation(
                    $type,
                    $this->mockTokens[$type][$roleName],
                    $roleName
                );
            }
        }
    }

    /**
     * Test that all roles for associated entities can do or not do what they should be able to.
     *
     * @return void
     */
    public function testAssociatedRoles()
    {
        $permissionMap = array(
            Voter::ACCESS_GRANTED => 'allow',
            Voter::ACCESS_DENIED => 'deny',
            Voter::ACCESS_ABSTAIN => 'abstain',
        );

        foreach ($this->roles as $type => $roleNames) {
            foreach ($roleNames as $roleName => $permissions) {
                foreach ($permissions as $permission => $expected) {
                    $this->assertEquals(
                        $expected,
                        $this->voter->vote(
                            $this->mockTokens[$type][$roleName],
                            $this->mockEntity,
                            array($permission)
                        ),
                        "For \"$type $roleName $permission\" expected: " . $permissionMap[$expected]
                    );
                }
            }
        }
    }

    /**
     * Creates a mock token with a mock Account and Person.
     *
     * @return TokenInterface
     */
    protected function createMockToken()
    {
        $person = \Mockery::mock('\Pelagos\Entity\Person');
        $person
            ->shouldReceive('isSameTypeAndId')
            ->andReturnUsing(
                function ($anotherPerson) use ($person) {
                    return $anotherPerson === $person;
                }
            );
        return \Mockery::mock(
            '\Symfony\Component\Security\Core\Authentication\Token\TokenInterface',
            array(
                'getUser' => \Mockery::mock(
                    '\Pelagos\Entity\Account',
                    array('getPerson' => $person)
                ),
            )
        );
    }

    /**
     * Create a mock Person Association of the specified type for the specified tooken and role.
     *
     * @param string         $type     The type of association.
     * @param TokenInterface $token    The token from which the Person can be retrieved.
     * @param string         $roleName The name of the role to assign.
     *
     * @return PersonAssociationInterface
     */
    protected function createMockPersonAssociation($type, TokenInterface $token, $roleName)
    {
        $personAssociation = \Mockery::mock(
            "\Pelagos\Entity\Person$type",
            array(
                'getPerson' => $token->getUser()->getPerson(),
                'getRole' => \Mockery::mock(
                    "\Pelagos\Entity\\$type" . 'Role',
                    array('getName' => $roleName)
                ),
            )
        );
        if ($type == 'DataRepository') {
            $token->getUser()->getPerson()
                ->shouldReceive('getPersonDataRepositories')
                ->andReturn(new ArrayCollection(array($personAssociation)));
        } else {
            $token->getUser()->getPerson()
                ->shouldReceive('getPersonDataRepositories')
                ->andReturn(new ArrayCollection);
        }
        return $personAssociation;
    }
}
