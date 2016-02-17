<?php

namespace Tests\unit\Pelagos\Bundle\AppBundle\Security;

use Doctrine\Common\Collections\ArrayCollection;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles as DR_Roles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\FundingOrganizationRoles as FO_Roles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles as RG_Roles;

use Pelagos\Bundle\AppBundle\Security\CreateAnythingVoter as Voter;

/**
 * Unit tests for the Research Group voter.
 */
class CreateAnythingVoterTest extends PelagosEntityVoterTest
{
    /**
     * The attributes that CreateAnythingVoter should support.
     *
     * @var array
     */
    protected $supportedAttributes = array(Voter::CAN_CREATE);

    /**
     * Set up run for each test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->voter = new Voter;

        $this->mockEntity = 'anything';

        $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_CREATE] = Voter::ACCESS_GRANTED;
        $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;

        $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;

        $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
    }

    /**
     * This voter supports any subject, so it should never abstain dependent on the subject.
     *
     * @return void
     */
    public function testAbstainForUnsupportedSubject()
    {
        // Nothing here because we are overriding this test that does not apply from the abstract class.
    }

    /**
     * Test that the voter denies access for a token that does not resolve to a user.
     *
     * @return void
     */
    public function testDenyBadUserCanCreate()
    {
        $this->assertEquals(
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                \Mockery::mock(
                    '\Symfony\Component\Security\Core\Authentication\Token\TokenInterface',
                    array('getUser' => null)
                ),
                $this->mockEntity,
                array(Voter::CAN_CREATE)
            )
        );
    }
}
