<?php

namespace App\Tests\Security;

use Doctrine\Common\Collections\ArrayCollection;

use App\Entity\DataRepositoryRole as DR_Roles;
use App\Entity\FundingOrganizationRole as FO_Roles;
use App\Entity\ResearchGroupRole as RG_Roles;

use App\Security\Voter\ResearchGroupVoter as Voter;

/**
 * Unit tests for the Research Group voter.
 */
class ResearchGroupVoterTest extends PelagosEntityVoterTestCase
{
    /**
     * The attributes that ResearchGroupVoter should support.
     *
     * @var array
     */
    protected $supportedAttributes = array(
        Voter::CAN_EDIT,
        Voter::CAN_DELETE,
    );

    /**
     * Set up run for each test.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;

        $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;

        $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_GRANTED;
        $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_GRANTED;
        $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_EDIT] = Voter::ACCESS_GRANTED;
        $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;

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
     * Test that the voter abstains for a research group that has no context.
     *
     * @return void
     */
    public function testAbstainForResearchGroupWithNoContext()
    {
        foreach ($this->supportedAttributes as $attribute) {
            $this->assertEquals(
                Voter::ACCESS_ABSTAIN,
                $this->voter->vote(
                    $this->mockTokens['DataRepository'][DR_Roles::MANAGER],
                    \Mockery::mock(
                        '\Pelagos\Entity\ResearchGroup',
                        array('getFundingCycle' => null)
                    ),
                    array($attribute)
                ),
                "Did not abstain for $attribute Research Group with no context"
            );
        }
    }
}
