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

    public function testExceptionEventHappenedNoParams()
    {
        $this->setExpectedException('Exception', 'Missing argument 1 for eventHappened()');
        eventHappened();
    }

    public function testExceptionEventHappenedOneParam()
    {
        $this->setExpectedException('Exception', 'Missing argument 2 for eventHappened()');
        eventHappened('foo');
    }

    public function testExceptionEventHappenedNullEvent()
    {
        $this->setExpectedException('Exception', 'Action not found');
        eventHappened(null, null);
    }

    public function testExceptionEventHappenedEmptyStringEvent()
    {
        $this->setExpectedException('Exception', 'Action not found');
        eventHappened('', null);
    }

    public function testEventHappenedValidEventNullData()
    {
        $this->assertEquals(null, eventHappened('dif_saved_and_submitted', null));
    }

    public function testEventHappenedValidEventEmptyData()
    {
        $this->assertEquals(null, eventHappened('dif_saved_and_submitted', array()));
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
