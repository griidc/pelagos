<?php

namespace App\Tests\Security;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Doctrine\Common\Collections\ArrayCollection;

use App\Entity\DataRepositoryRole as DR_Roles;
use App\Entity\FundingOrganizationRole as FO_Roles;
use App\Entity\ResearchGroupRole as RG_Roles;

use App\Security\Voter\PelagosEntityVoter as Voter;

use App\Entity\PersonAssociationInterface;
use App\Entity\Entity;

/**
 * Abstract class containing utility methods and tests shared by all Pelagos Entity voter tests.
 */
abstract class PelagosEntityVoterTest extends TestCase
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
     * The attributes that the voter should support.
     *
     * This should be overridden with the attributes supported by each voter.
     *
     * @var array
     */
    protected $supportedAttributes = array();

    /**
     * Set up run for each test.
     *
     * @return void
     */
    public function setUp(): void
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

//    /**
//     * Test that all roles for associated entities can do or not do what they should be able to.
//     *
//     * @return void
//     */
//    public function testAssociatedRoles()
//    {
//        $permissionMap = array(
//            Voter::ACCESS_GRANTED => 'allow',
//            Voter::ACCESS_DENIED => 'deny',
//            Voter::ACCESS_ABSTAIN => 'abstain',
//        );
//
//        foreach ($this->roles as $type => $roleNames) {
//            foreach ($roleNames as $roleName => $permissions) {
//                foreach ($permissions as $permission => $expected) {
//                    $this->assertEquals(
//                        $expected,
//                        $this->voter->vote(
//                            $this->mockTokens[$type][$roleName],
//                            $this->mockEntity,
//                            array($permission)
//                        ),
//                        "For \"$type $roleName $permission\" expected: " . $permissionMap[$expected]
//                    );
//                }
//            }
//        }
//    }

    /**
     * Test that the voter abstains for a subject that is not supported.
     *
     * @return void
     */
    public function testAbstainForUnsupportedSubject()
    {
        foreach ($this->supportedAttributes as $attribute) {
            $this->assertEquals(
                Voter::ACCESS_ABSTAIN,
                $this->voter->vote(
                    $this->mockTokens['DataRepository'][DR_Roles::MANAGER],
                    \Mockery::mock('UnsupportedSubject'),
                    array($attribute)
                ),
                "Did not abstain for $attribute an unsupported subject"
            );
        }
    }

    /**
     * Test that the voter abstains for an attribute it doesn't support.
     *
     * @return void
     */
    public function testAbstainForUnsupportedAttribute()
    {
        $this->assertEquals(
            Voter::ACCESS_ABSTAIN,
            $this->voter->vote(
                $this->mockTokens['DataRepository'][DR_Roles::MANAGER],
                $this->mockEntity,
                array('THIS_IS_AN_UNSUPPORTED_ATTRIBUTE')
            )
        );
    }

//    /**
//     * Test that the voter denies access for a token that does not resolve to a user.
//     *
//     * @return void
//     */
//    public function testDenyBadUser()
//    {
//        foreach ($this->supportedAttributes as $attribute) {
//            $this->assertEquals(
//                Voter::ACCESS_DENIED,
//                $this->voter->vote(
//                    \Mockery::mock(
//                        '\Symfony\Component\Security\Core\Authentication\Token\TokenInterface',
//                        array('getUser' => null)
//                    ),
//                    $this->mockEntity,
//                    array($attribute)
//                ),
//                "Did not deny $attribute for a bad user token"
//            );
//        }
//    }

//    /**
//     * Test that the voter denies access to a Person with no roles.
//     *
//     * @return void
//     */
//    public function testDenyPersonWithNoRoles()
//    {
//        foreach ($this->supportedAttributes as $attribute) {
//            $mockToken = $this->createMockToken();
//            $mockToken
//                ->getUser()
//                ->getPerson()
//                ->shouldReceive('getPersonDataRepositories')
//                ->andReturn(new ArrayCollection);
//            $mockToken
//                ->getUser()
//                ->getPerson()
//                ->shouldReceive('getPersonResearchGroups')
//                ->andReturn(new ArrayCollection);
//            $mockToken
//                ->getUser()
//                ->getPerson()
//                ->shouldReceive('getResearchGroups')
//                ->andReturn(array());
//            $this->assertEquals(
//                Voter::ACCESS_DENIED,
//                $this->voter->vote(
//                    $mockToken,
//                    $this->mockEntity,
//                    array($attribute)
//                ),
//                "Did not deny $attribute for a Person with no roles"
//            );
//        }
//    }

    /**
     * Creates a mock Person for use by extension classes and by createMockToken function.
     *
     * @see createMockToken
     *
     * @return TokenInterface
     */
    protected function createMockPerson()
    {
        $person = \Mockery::mock('\App\Entity\Person');
        $person
            ->shouldReceive('isSameTypeAndId')
            ->andReturnUsing(
                function ($anotherPerson) use ($person) {
                    return $anotherPerson === $person;
                }
            );
        return $person;
    }

    /**
     * Creates a mock token with a mock Account and Person.
     *
     * @return TokenInterface
     */
    protected function createMockToken()
    {
        $person = $this->createMockPerson();
        return \Mockery::mock(
            '\Symfony\Component\Security\Core\Authentication\Token\TokenInterface',
            array(
                'getUser' => \Mockery::mock(
                    '\App\Entity\Account',
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
            "\App\Entity\Person$type",
            array(
                'getPerson' => $token->getUser()->getPerson(),
                'getRole' => \Mockery::mock(
                    "\App\Entity\\$type" . 'Role',
                    array('getName' => $roleName)
                ),
            )
        );
        $personAssociation->shouldReceive('isSameTypeAndId')->andReturnUsing(
            function ($somethingElse) use ($personAssociation) {
                return $personAssociation === $somethingElse;
            }
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
        if ($type == 'ResearchGroup') {
            $token->getUser()->getPerson()
                ->shouldReceive('getPersonResearchGroups')
                ->andReturn(new ArrayCollection(array($personAssociation)));
            $token->getUser()->getPerson()
                ->shouldReceive('getResearchGroups')
                ->andReturn(array(\Mockery::mock('\App\Entity\ResearchGroup')));
        } else {
            $token->getUser()->getPerson()
                ->shouldReceive('getPersonResearchGroups')
                ->andReturn(new ArrayCollection);
            $token->getUser()->getPerson()
                ->shouldReceive('getResearchGroups')
                ->andReturn(array());
        }
        return $personAssociation;
    }
}
