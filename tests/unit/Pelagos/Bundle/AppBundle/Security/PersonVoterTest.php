<?php


namespace Tests\unit\Pelagos\Bundle\AppBundle\Security;

use Doctrine\Common\Collections\ArrayCollection;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles as DR_Roles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\FundingOrganizationRoles as FO_Roles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles as RG_Roles;
use Pelagos\Bundle\AppBundle\Security\PersonVoter as Voter;
use Pelagos\Bundle\AppBundle\Security\PersonVoter as PersonVoter;

/**
 * Class PersonVoterTest Short name goes here. (Includes - GRIIDC PHP CLASS DOC).
 *
 * A longer more detailed description that can span
 * multiple lines goes here.
 *
 * @package Tests\unit\Pelagos\Bundle\AppBundle\Security
 */
class PersonVoterTest extends PelagosEntityVoterTest
{
    const SAME_ID = 1;
    const SOME_OTHER_ID = 2;

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
    public function setUp()
    {
        parent::setUp();

        $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        // $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;
        $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        // $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;
        $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        // $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;
        $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        // $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;

        $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        // $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;
        $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        //  $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;
        $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        //  $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;

        $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        // $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;
        $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        //  $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;
        $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        //  $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;
        $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        //  $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_DELETE] = Voter::ACCESS_ABSTAIN;

        $this->voter = new \Pelagos\Bundle\AppBundle\Security\PersonVoter;

        // Mock a Person and make the DataRepository association
        $this->mockEntity = \Mockery::mock(
            '\Pelagos\Entity\Person',
            array(
                'getDataRepositories' => array(\Mockery::mock(
                    '\Pelagos\Entity\DataRepository',
                    array(
                        'getPersonDataRepositories' => new ArrayCollection(
                            $this->personAssociations['DataRepository']
                        ),
                    )
                )),

            )
        );
        $person = $this->mockEntity;
        $this->mockEntity->shouldReceive('isSameTypeAndId')
            ->andReturnUsing(
                function ($anotherPerson) use ($person) {
                    return $anotherPerson === $person;
                }
            );
    }

    /**
     * Creates a mock Person with an ID.
     *
     * @param integer $id The id used for testing.
     *
     * @return \Mockery\MockInterface Return the mock Person.
     */
    private function createMockPerson($id)
    {
        // Mock a Person and make the DataRepository association
        $person = \Mockery::mock('\Pelagos\Entity\Person');
        $person->shouldReceive(array('getId' => $id));
        $person->shouldReceive('isSameTypeAndId')
            ->andReturnUsing(
                function ($anotherPerson) use ($person) {
                    return (get_class($person) == get_class($anotherPerson) &&
                        $person->getId() == $anotherPerson->getId());
                }
            );
        return $person;
    }

    /**
     * Creates a mock token with a mock Account and Person.
     *
     * @param integer $id The id used for testing.
     *
     * @return \Mockery\MockInterface Return the mock TokenInterface created.
     */
    protected function localCreateMockToken($id)
    {
        $person = $this->createMockPerson($id);
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
        $mockToken = $this->localCreateMockToken(self::SAME_ID);
        $this->createMockPersonAssociation('DataRepository', $mockToken, 'Manager');
        $sameIdPerson = $this->createMockPerson(self::SAME_ID);

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
        $mockToken = $this->localCreateMockToken(self::SAME_ID);
        $this->createMockPersonAssociation('DataRepository', $mockToken, 'Manager');
        $personWithDifferentId = $this->createMockPerson(self::SOME_OTHER_ID);

        $this->assertEquals(
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                $mockToken,
                $personWithDifferentId,
                array(Voter::CAN_EDIT)
            )
        );
    }
}
