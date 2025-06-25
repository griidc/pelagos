<?php

namespace App\Tests\Security;

use Doctrine\Common\Collections\ArrayCollection;

use App\Entity\DataRepositoryRole as DR_Roles;
use App\Entity\FundingOrganizationRole as FO_Roles;
use App\Entity\ResearchGroupRole as RG_Roles;
use App\Security\Voter\DatasetSubmissionVoter as Voter;
use App\Security\Voter\DatasetSubmissionVoter as DatasetSubmissionVoter;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\ResearchGroup;

/**
 * Class PersonVoterTest.
 */
class DatasetSubmissionVoterTest extends PelagosEntityVoterTestCase
{
    /**
     * The attributes that ResearchGroupVoter should support.
     *
     * @var array
     */
    protected $supportedAttributes = array(
        Voter::CAN_CREATE,
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
        $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::MANAGER][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::ENGINEER][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SUPPORT][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['DataRepository'][DR_Roles::SME][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;

        $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::LEADERSHIP][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADVISORY][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['FundingOrganization'][FO_Roles::ADMIN][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;

        $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::LEADERSHIP][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::ADMIN][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::DATA][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_CREATE] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_EDIT] = Voter::ACCESS_DENIED;
        $this->roles['ResearchGroup'][RG_Roles::RESEARCHER][Voter::CAN_DELETE] = Voter::ACCESS_DENIED;

        $this->voter = new DatasetSubmissionVoter;

        // Mock a PersonResearchGroup and build the tree.
        $this->mockEntity = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getDataset' => \Mockery::mock(
                    Dataset::class,
                    array(
                        'getResearchGroup' => \Mockery::mock(
                            ResearchGroup::class,
                            array(
                                'isSameTypeAndId' => false,
                                'isLocked' => false,
                            )
                        ),
                    )
                ),
                'getStatus' => DatasetSubmission::STATUS_INCOMPLETE,
            )
        );
    }

    /**
     * Test can create and edit for user's research group.
     *
     * Test if a user can create a Dataset Submission for a dataset associated
     * with a research group that they are associated with.
     *
     * @return void
     */
    public function testCanCreateAndEditSameResearchGroup()
    {
        $mockToken = $this->createMockToken();
        $this->createMockPersonAssociation('ResearchGroup', $mockToken, RG_Roles::RESEARCHER);

        $datasetSubmission = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getDataset' => \Mockery::mock(
                    Dataset::class,
                    array(
                        'getResearchGroup' => \Mockery::mock(
                            ResearchGroup::class,
                            array(
                                'isSameTypeAndId' => true,
                                'isLocked' => false,
                            )
                        ),
                    )
                ),
                'getStatus' => DatasetSubmission::STATUS_INCOMPLETE,
            )
        );

        $this->assertEquals(
            Voter::ACCESS_GRANTED,
            $this->voter->vote(
                $mockToken,
                $datasetSubmission,
                array(Voter::CAN_CREATE)
            )
        );
        $this->assertEquals(
            Voter::ACCESS_GRANTED,
            $this->voter->vote(
                $mockToken,
                $datasetSubmission,
                array(Voter::CAN_EDIT)
            )
        );
    }

    /**
     * Test can create and edit for other research group.
     *
     * Test if a user can create a Dataset Submission for a dataset associated
     * with a research group that they are NOT associated with.
     *
     * @return void
     */
    public function testCanCreateAndEditOtherResearchGroup()
    {
        $mockToken = $this->createMockToken();
        $this->createMockPersonAssociation('ResearchGroup', $mockToken, RG_Roles::RESEARCHER);

        $datasetSubmission = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getDataset' => \Mockery::mock(
                    Dataset::class,
                    array(
                        'getResearchGroup' => \Mockery::mock(
                            ResearchGroup::class,
                            array(
                                'isSameTypeAndId' => false,
                                'isLocked' => false,
                            )
                        ),
                    )
                ),
                'getStatus' => DatasetSubmission::STATUS_INCOMPLETE,
            )
        );

        $this->assertEquals(
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                $mockToken,
                $datasetSubmission,
                array(Voter::CAN_CREATE)
            )
        );
        $this->assertEquals(
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                $mockToken,
                $datasetSubmission,
                array(Voter::CAN_EDIT)
            )
        );
    }

    /**
     * Test can delete an incomplete submission for user's research group.
     *
     * Test if a user can delete an incomplete Dataset Submission for a dataset
     * associated with a research group that they are associated with.
     *
     * @return void
     */
    public function testCanDeleteIncomplete()
    {
        $mockToken = $this->createMockToken();
        $this->createMockPersonAssociation('ResearchGroup', $mockToken, RG_Roles::RESEARCHER);

        $datasetSubmission = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getDataset' => \Mockery::mock(
                    Dataset::class,
                    array(
                        'getResearchGroup' => \Mockery::mock(
                            ResearchGroup::class,
                            array(
                                'isSameTypeAndId' => true,
                                'isLocked' => false,
                            )
                        ),
                    )
                ),
                'getStatus' => DatasetSubmission::STATUS_INCOMPLETE,
            )
        );

        $this->assertEquals(
            Voter::ACCESS_GRANTED,
            $this->voter->vote(
                $mockToken,
                $datasetSubmission,
                array(Voter::CAN_DELETE)
            )
        );
    }

    /**
     * Test can not delete a complete submission for user's research group.
     *
     * Test the a user can not delete a complete Dataset Submission for a dataset
     * associated with a research group that they are associated with.
     *
     * @return void
     */
    public function testCanNotDeleteComplete()
    {
        $mockToken = $this->createMockToken();
        $this->createMockPersonAssociation('ResearchGroup', $mockToken, RG_Roles::RESEARCHER);

        $datasetSubmission = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getDataset' => \Mockery::mock(
                    Dataset::class,
                    array(
                        'getResearchGroup' => \Mockery::mock(
                            ResearchGroup::class,
                            array(
                                'isSameTypeAndId' => true,
                                'isLocked' => false,
                            )
                        ),
                    )
                ),
                'getStatus' => DatasetSubmission::STATUS_COMPLETE,
            )
        );

        $this->assertEquals(
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                $mockToken,
                $datasetSubmission,
                array(Voter::CAN_DELETE)
            )
        );
    }

    /**
     * Test can create and edit for locked research group.
     *
     * Test if a user can NOT create a Dataset Submission for a dataset associated
     * because the research group is locked.
     *
     * @return void
     */
    public function testCanNotCreateAndEditLockedResearchGroup()
    {
        $mockToken = $this->createMockToken();
        $this->createMockPersonAssociation('ResearchGroup', $mockToken, RG_Roles::RESEARCHER);

        $datasetSubmission = \Mockery::mock(
            DatasetSubmission::class,
            array(
                'getDataset' => \Mockery::mock(
                    Dataset::class,
                    array(
                        'getResearchGroup' => \Mockery::mock(
                            ResearchGroup::class,
                            array(
                                'isSameTypeAndId' => false,
                                'isLocked' => true,
                            )
                        ),
                    )
                ),
                'getStatus' => DatasetSubmission::STATUS_INCOMPLETE,
            )
        );

        $this->assertEquals(
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                $mockToken,
                $datasetSubmission,
                array(Voter::CAN_CREATE)
            )
        );
        $this->assertEquals(
            Voter::ACCESS_DENIED,
            $this->voter->vote(
                $mockToken,
                $datasetSubmission,
                array(Voter::CAN_EDIT)
            )
        );
    }
}
