<?php

namespace Tests\unit\Pelagos\Bundle\AppBundle\Security;

use Doctrine\Common\Collections\ArrayCollection;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles as DR_Roles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\FundingOrganizationRoles as FO_Roles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles as RG_Roles;

use Pelagos\Bundle\AppBundle\Security\ResearchGroupVoter as Voter;

/**
 * Unit tests for the Research Group voter.
 */
class ResearchGroupVoterTest extends PelagosEntityVoterTest
{
    /**
     * Set up run for each test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_EDIT] = Voter::ACCESS_GRANTED;
        $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;

        $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;

        $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_GRANTED;
        $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_GRANTED;
        $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_EDIT] = Voter::ACCESS_GRANTED;
        $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;

        $this->voter = new Voter;

        // Mock a ResearchGroup and build the tree.
        $this->mockEntity = \Mockery::mock(
            '\Pelagos\Entity\ResearchGroup',
            array(
                'getPersonResearchGroups' => new ArrayCollection(
                    $this->personAssociations['ResearchGroup']
                ),
                'getFundingCycle' => \Mockery::mock(
                    '\Pelagos\Entity\FundingCycle',
                    array(
                        'getFundingOrganization' => \Mockery::mock(
                            '\Pelagos\Entity\FundingOrganization',
                            array(
                                'getPersonFundingOrganizations' => new ArrayCollection(
                                    $this->personAssociations['FundingOrganization']
                                ),
                                'getDataRepository' => \Mockery::mock(
                                    '\Pelagos\Entity\DataRepository',
                                    array(
                                        'getPersonDataRepositories' => new ArrayCollection(
                                            $this->personAssociations['DataRepository']
                                        ),
                                    )
                                ),
                            )
                        ),
                    )
                ),
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
            Voter::ACCESS_ABSTAIN,
            $this->voter->vote(
                $this->mockTokens['DataRepository'][DR_Roles::MANAGER],
                \Mockery::mock('\Pelagos\Entity\Entity'),
                array(Voter::CAN_EDIT)
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
            Voter::ACCESS_ABSTAIN,
            $this->voter->vote(
                $this->mockTokens['DataRepository'][DR_Roles::MANAGER],
                $this->mockEntity,
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
            Voter::ACCESS_ABSTAIN,
            $this->voter->vote(
                $this->mockTokens['DataRepository'][DR_Roles::MANAGER],
                \Mockery::mock(
                    '\Pelagos\Entity\ResearchGroup',
                    array('getFundingCycle' => null)
                ),
                array(Voter::CAN_EDIT)
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
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                \Mockery::mock(
                    '\Symfony\Component\Security\Core\Authentication\Token\TokenInterface',
                    array('getUser' => null)
                ),
                $this->mockEntity,
                array(Voter::CAN_EDIT)
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
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                $this->createMockToken(),
                $this->mockEntity,
                array(Voter::CAN_EDIT)
            )
        );
    }
}
