<?php

namespace Tests\unit\Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use Doctrine\Common\Collections\ArrayCollection;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;
use Pelagos\Bundle\AppBundle\Security\ResearchGroupVoter;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\PersonAssociationInterface;

/**
 * Unit tests for the Research Group voter.
 */
class ResearchGroupVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The instance of ResearchGroupVoter under test.
     *
     * @var ResearchGroupVoter
     */
    protected $researchGroupVoter;

    /**
     * An array of mock tokens for Persons with various roles.
     *
     * @var array
     */
    protected $mockTokens;

    /**
     * Property to hold a mock Research Group for testing.
     *
     * @var ResearchGroup
     */
    protected $mockResearchGroup;

    /**
     * Property to hold a second mock Research Group for testing.
     *
     * @var ResearchGroup
     */
    protected $mockResearchGroup2;

    /**
     * Set up run for each test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->researchGroupVoter = new ResearchGroupVoter;

        $roles = array(
            'DataRepository' => array(
                DataRepositoryRoles::MANAGER,
                DataRepositoryRoles::ENGINEER,
                DataRepositoryRoles::SUPPORT,
                DataRepositoryRoles::SME,
            ),
            'ResearchGroup' => array(
                ResearchGroupRoles::LEADERSHIP,
                ResearchGroupRoles::ADMIN,
                ResearchGroupRoles::DATA,
                ResearchGroupRoles::RESEARCHER,
            ),
        );

        $this->mockTokens = array();
        $personAssociations = array();

        foreach ($roles as $type => $roleNames) {
            $this->mockTokens[$type] = array();
            $personAssociations[$type] = array();
            foreach ($roleNames as $roleName) {
                $this->mockTokens[$type][$roleName] = $this->createMockToken();
                $personAssociations[$type][] = $this->createMockPersonAssociation(
                    $type,
                    $this->mockTokens[$type][$roleName],
                    $roleName
                );
            }
        }

        // Mock a ResearchGroup and build the tree.
        $this->mockResearchGroup = \Mockery::mock(
            '\Pelagos\Entity\ResearchGroup',
            array(
                'getPersonResearchGroups' => new ArrayCollection(
                    $personAssociations['ResearchGroup']
                ),
                'getFundingCycle' => \Mockery::mock(
                    '\Pelagos\Entity\FundingCycle',
                    array(
                        'getFundingOrganization' => \Mockery::mock(
                            '\Pelagos\Entity\FundingOrganization',
                            array(
                                'getDataRepository' => \Mockery::mock(
                                    '\Pelagos\Entity\DataRepository',
                                    array(
                                        'getPersonDataRepositories' => new ArrayCollection(
                                            $personAssociations['DataRepository']
                                        ),
                                    )
                                ),
                            )
                        ),
                    )
                ),
            )
        );

        // Create a mock Token for a Person that will be given the role of Research Group Leadership.
        $this->mockTokens['ResearchGroup2'][ResearchGroupRoles::LEADERSHIP] = $this->createMockToken();

        $this->mockResearchGroup2 = \Mockery::mock(
            '\Pelagos\Entity\ResearchGroup',
            array(
                'getPersonResearchGroups' => new ArrayCollection(
                    array(
                        $this->createMockPersonAssociation(
                            'ResearchGroup',
                            $this->mockTokens['ResearchGroup2'][ResearchGroupRoles::LEADERSHIP],
                            ResearchGroupRoles::LEADERSHIP
                        ),
                    )
                ),
                'getFundingCycle' => $this->mockResearchGroup->getFundingCycle(),
            )
        );
    }

    /**
     * Test that the voter abstains for something that is not a ResearchGroup.
     *
     * @return void
     */
    public function testAbstainForNonResearchGroup()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->researchGroupVoter->vote(
                $this->mockTokens['DataRepository'][DataRepositoryRoles::MANAGER],
                \Mockery::mock('\Pelagos\Entity\Entity'),
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Test that the voter abstains for an attribute it doesn't support.
     *
     * @return void
     */
    public function testAbstainForUnsupportedAttribute()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->researchGroupVoter->vote(
                $this->mockTokens['DataRepository'][DataRepositoryRoles::MANAGER],
                $this->mockResearchGroup,
                array('THIS_IS_AN_UNSUPPORTED_ATTRIBUTE')
            )
        );
    }

    /**
     * Test that the voter abstains for a research group that has no context.
     *
     * @return void
     */
    public function testAbstainForResearchGroupWithNoContext()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->researchGroupVoter->vote(
                $this->mockTokens['DataRepository'][DataRepositoryRoles::MANAGER],
                \Mockery::mock(
                    '\Pelagos\Entity\ResearchGroup',
                    array('getFundingCycle' => null)
                ),
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Test that the voter denies access for a token that does not resolve to a user.
     *
     * @return void
     */
    public function testDenyBadUserCanEditResearchGroup()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->researchGroupVoter->vote(
                \Mockery::mock(
                    '\Symfony\Component\Security\Core\Authentication\Token\TokenInterface',
                    array('getUser' => null)
                ),
                $this->mockResearchGroup,
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Test that the voter denies access to a Person with no roles.
     *
     * @return void
     */
    public function testDenyPersonWithNoRolesCanEditResearchGroup()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->researchGroupVoter->vote(
                $this->createMockToken(),
                $this->mockResearchGroup,
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Test that the voter grants access for CAN_EDIT to Data Repository Managers.
     *
     * @return void
     */
    public function testGrantCanEditToDataRepositoryManager()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->researchGroupVoter->vote(
                $this->mockTokens['DataRepository'][DataRepositoryRoles::MANAGER],
                $this->mockResearchGroup,
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Test that the voter grants access for CAN_EDIT to Research Group Leadership.
     *
     * @return void
     */
    public function testGrantCanEditToResearchGroupLeadership()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->researchGroupVoter->vote(
                $this->mockTokens['ResearchGroup'][ResearchGroupRoles::LEADERSHIP],
                $this->mockResearchGroup,
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Test that the voter grants access for CAN_EDIT to Research Group Administration.
     *
     * @return void
     */
    public function testGrantCanEditToResearchGroupAdmin()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->researchGroupVoter->vote(
                $this->mockTokens['ResearchGroup'][ResearchGroupRoles::ADMIN],
                $this->mockResearchGroup,
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Test that the voter grants access for CAN_EDIT to Research Group Data Managers.
     *
     * @return void
     */
    public function testGrantCanEditToResearchGroupData()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->researchGroupVoter->vote(
                $this->mockTokens['ResearchGroup'][ResearchGroupRoles::DATA],
                $this->mockResearchGroup,
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Test that the voter denies access for CAN_EDIT to Research Group Researchers.
     *
     * @return void
     */
    public function testDenyCanEditToOtherResearchGroupResearcher()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->researchGroupVoter->vote(
                $this->mockTokens['ResearchGroup'][ResearchGroupRoles::RESEARCHER],
                $this->mockResearchGroup,
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Test that the voter denies access for CAN_EDIT to Research Group Leadership of another Research Group.
     *
     * @return void
     */
    public function testDenyCanEditToOtherResearchGroupLeadership()
    {
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->researchGroupVoter->vote(
                $this->mockTokens['ResearchGroup2'][ResearchGroupRoles::LEADERSHIP],
                $this->mockResearchGroup,
                array(ResearchGroupVoter::CAN_EDIT)
            )
        );
    }

    /**
     * Creates a mock token with a mock Account and Person.
     *
     * @return TokenInterface
     */
    private function createMockToken()
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
    private function createMockPersonAssociation($type, TokenInterface $token, $roleName)
    {
        return \Mockery::mock(
            "\Pelagos\Entity\Person$type",
            array(
                'getPerson' => $token->getUser()->getPerson(),
                'getRole' => \Mockery::mock(
                    "\Pelagos\Entity\\$type" . 'Role',
                    array('getName' => $roleName)
                ),
            )
        );
    }
}
