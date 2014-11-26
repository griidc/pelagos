<?php

namespace Pelagos\DataManagers;

/**
 * @runTestsInSeparateProcesses
 */

class EventHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        require_once 'EventHandler.php';
        require_once 'stubs/DataManagersStub.php';
        require_once 'stubs/griidcMailerStub.php';
        require_once 'stubs/ResearchConsortiaStub.php';
        require_once 'stubs/RISStub.php';
        require_once 'stubs/DBUtilsStub.php';
    }

    public function testExceptionEventHappenedNullEvent()
    {
        $this->setExpectedException('Exception', 'Action not found');
        eventHappened(null, array());
    }

    public function testExceptionEventHappenedEmptyStringEvent()
    {
        $this->setExpectedException('Exception', 'Action not found');
        eventHappened('', array());
    }

    public function testExceptionEventHappenedValidEvents()
    {
        $this->setExpectedException('Exception', '"Lipphardt, Bruce" <brucel@udel.edu>');
        eventHappened('dif_saved_and_submitted', array('userId' => 'schen'));
        eventHappened('dif_approved', array('userId' => 'schen'));
        eventHappened('dataset_registration_submitted', array('userId' => 'schen'));
        eventHappened('dataset_registration_updated', array('userId' => 'schen'));
        eventHappened('dif_saved_but_not_submitted', array('userId' => 'schen'));
        eventHappened('dif_unlock_request_approved', array('userId' => 'schen'));
        eventHappened('doi_requested', array('userId' => 'schen'));
        eventHappened('account_requested', array('userId' => 'schen'));
        eventHappened('account_request_approved', array('userId' => 'schen'));
        eventHappened('dif_unlock_requested', array('userId' => 'schen'));
        eventHappened('doi_approved', array('userId' => 'schen'));
    }
}
