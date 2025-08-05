<?php

namespace App\Tests\Security;

use App\Entity\DataRepositoryRole as DR_Roles;
use App\Entity\FundingOrganizationRole as FO_Roles;
use App\Entity\ResearchGroupRole as RG_Roles;
use App\Security\Voter\PersonVoter as Voter;
use App\Security\Voter\PersonVoter as PersonVoter;

/**
 * Class PersonVoterTest.
 */
class PersonVoterTest extends PelagosEntityVoterTestCase
{
    /**
     * The attributes that ResearchGroupVoter should support.
     *
     * @var array
     */
    protected $supportedAttributes = array(
        Voter::CAN_EDIT,
    );

    /**
     * Set up run for each test.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        //  change the expectations set by setup in the base class
        $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;

        $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;

        $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;

        $this->voter = new PersonVoter;

        // Mock a Person and make the DataRepository association to satisfy base class expectations
        $this->mockEntity = $this->createMockPerson();
    }

    /**
     * Test if a user can edit it's own Person object.
     *
     * Test that the voter returns ACCESS_GRANTED when
     * the action is CAN_EDIT and the Person subject to be edited
     * is the same object as the users Person.
     *
     * @return void
     */
    public function testCanUserEditSelf()
    {
        $mockToken = $this->createMockToken();
        $this->createMockPersonAssociation('DataRepository', $mockToken, 'Manager');
        //  get the Person out of the Token for comparison as the subject
        $sameIdPerson = $mockToken->getUser()->getPerson();

        $this->assertEquals(
            Voter::ACCESS_GRANTED,
            $this->voter->vote(
                $mockToken,
                $sameIdPerson,
                array(Voter::CAN_EDIT)
            )
        );
    }

    /**
     * Test if a user can edit it's own Person object.
     *
     * Test that the voter returns ACCESS_DENIED when
     * the action is CAN_EDIT and the Person subject to be edited
     * is NOT the same object as the users Person.
     *
     * @return void
     */
    public function testCanUserEditOtherPerson()
    {
        $mockToken = $this->createMockToken();
        $this->createMockPersonAssociation('DataRepository', $mockToken, 'Manager');
        $otherPerson = $this->createMockPerson();

        $this->assertEquals(
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                $mockToken,
                $otherPerson,
                array(Voter::CAN_EDIT)
            )
        );
    }
}
