<?php

namespace Pelagos\DataManagers;

/**
 * @runTestsInSeparateProcesses
 */

class EventHandlerTest extends \PHPUnit_Framework_TestCase
{
    # all events, event-dependent tests to perform, and extra info for tests
    private $events = array(
        'account_requested' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName','userRCList'              ),
            'templateRegEx' => '/has received an account creation request from/'
        ),
        'account_request_approved' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName','userRCList'              ),
            'templateRegEx' => '/has completed the .* account creation process/'
        ),
        'dif_saved_but_not_submitted' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName','userRCList','udi','udiRC'),
            'templateRegEx' => '/a Dataset Information Form \(DIF\) has been created/'
        ),
        'dif_saved_and_submitted' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName','userRCList','udi','udiRC'),
            'templateRegEx' => '/A Dataset Information Form \(DIF\) has been saved and submitted/'
        ),
        'dif_approved' => array(
            'tests' => array('template','dmEmail','dmName',                                            'udi','udiRC'),
            'templateRegEx' => '/A Dataset Information Form for dataset  has been approved/'
        ),
        'dif_unlock_requested' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName','userRCList','udi','udiRC'),
            'templateRegEx' => '/has received a request to unlock the Dataset Information Form \(DIF\)/'
        ),
        'dif_unlock_request_approved' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName',             'udi'        ),
            'templateRegEx' => '/has approved a request to unlock the Dataset Information Form \(DIF\)/'
        ),
        'dataset_registration_submitted' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName','userRCList','udi','udiRC'),
            'templateRegEx' => '/A registration form has been submitted/'
        ),
        'dataset_registration_updated' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName','userRCList','udi','udiRC'),
            'templateRegEx' => '/A registration form for the dataset .* has been updated/'
        ),
        'doi_requested' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName','userRCList'              ),
            'templateRegEx' => '/A Digital Object Identifier \(DOI\) has been requested for/'
        ),
        'doi_approved' => array(
            'tests' => array('template','dmEmail','dmName','userFirstName','userLastName','userRCList'              ),
            'templateRegEx' => '/has approved the request for a Digital Object Identifier \(DOI\)/'
        )
    );

    protected function setUp()
    {
        # add parent directory to include path so tests can be run from anywhere
        set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
        require_once 'EventHandler.php';
        require_once 'stubs/DataManagersStub.php';
        require_once 'stubs/griidcMailerStub.php';
        require_once 'stubs/ResearchConsortiaStub.php';
        require_once 'stubs/RISStub.php';
        require_once 'stubs/DBUtilsStub.php';
        require_once 'stubs/datasetsStub.php';
        require_once 'stubs/ldapStub.php';
    }

    # bad argument tests

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Missing argument 1 for eventHappened()
     */
    public function testEventHappenedNoArguments()
    {
        eventHappened();
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Missing argument 2 for eventHappened()
     */
    public function testEventHappenedOneArgument()
    {
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

    # tests for all events

    public function testEventHappenedNullData()
    {
        foreach ($this->events as $event => $testInfo) {
            $this->assertEquals(null, eventHappened($event, null));
        }
    }

    public function testEventHappenedEmptyData()
    {
        foreach ($this->events as $event => $testInfo) {
            $this->assertEquals(null, eventHappened($event, array()));
        }
    }

    public function testEventHappenedNullUDI()
    {
        foreach ($this->events as $event => $testInfo) {
            $this->assertEquals(null, eventHappened($event, array('udi' => null)));
        }
    }

    public function testEventHappenedBadUDI()
    {
        foreach ($this->events as $event => $testInfo) {
            $this->assertEquals(null, eventHappened($event, array('udi' => '1234')));
        }
    }

    public function testEventHappenedNullUserId()
    {
        foreach ($this->events as $event => $testInfo) {
            $this->assertEquals(null, eventHappened($event, array('userId' => null)));
        }
    }

    public function testEventHappenedUnknownUserId()
    {
        foreach ($this->events as $event => $testInfo) {
            $this->assertEquals(null, eventHappened($event, array('userId' => 'foobarbaz')));
        }
    }

    public function testEventHappenedNullrisUserId()
    {
        foreach ($this->events as $event => $testInfo) {
            $this->assertEquals(null, eventHappened($event, array('risUserId' => null)));
        }
    }

    public function testEventHappenedUnknownrisUserId()
    {
        foreach ($this->events as $event => $testInfo) {
            $this->assertEquals(null, eventHappened($event, array('risUserId' => 0)));
        }
    }

    # event-dependent tests

    public function testEventHappenedCorrectTemplate()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('template', $testInfo['tests'])) {
                foreach (array(array('userId' => 'user1'),array('risUserId' => 1)) as $data) {
                    print "\n\nTesting event: $event\n\nwith data:\n\n";
                    foreach ($data as $key => $val) {
                        print "  $key => $val\n";
                    }
                    print "\n\n";
                    eventHappened($event, $data);
                    $this->expectOutputRegex($testInfo['templateRegEx']);
                }
            }
        }
    }

    public function testEventHappenedSingleDMEmail()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('dmEmail', $testInfo['tests'])) {
                foreach (array(array('userId' => 'user1'),array('risUserId' => 1)) as $data) {
                    print "\n\nTesting event: $event\n\nwith data:\n\n";
                    foreach ($data as $key => $val) {
                        print "  $key => $val\n";
                    }
                    print "\n\n";
                    eventHappened($event, $data);
                    $this->expectOutputRegex('/To:.*dm1@somewhere.edu/');
                }
            }
        }
    }

    public function testEventHappenedMultipleDMsEmail()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('dmEmail', $testInfo['tests'])) {
                foreach (array(array('userId' => 'user2'),array('risUserId' => 2)) as $data) {
                    print "\n\nTesting event: $event\n\nwith data:\n\n";
                    foreach ($data as $key => $val) {
                        print "  $key => $val\n";
                    }
                    print "\n\n";
                    eventHappened($event, $data);
                    $this->expectOutputRegex('/To:[^\n]*dm2@somewhere\.edu.*To:[^\n]*dm3@somewhere\.edu/ms');
                }
            }
        }
    }

    public function testEventHappenedDMName()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('dmName', $testInfo['tests'])) {
                foreach (array(array('userId' => 'user1'),array('risUserId' => 1)) as $data) {
                    print "\n\nTesting event: $event\n\nwith data:\n\n";
                    foreach ($data as $key => $val) {
                        print "  $key => $val\n";
                    }
                    print "\n\n";
                    eventHappened($event, $data);
                    $this->expectOutputRegex('/Dear Data Manager 1,/');
                }
            }
        }
    }

    public function testEventHappenedUserFirstName()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('userFirstName', $testInfo['tests'])) {
                eventHappened(
                    $event,
                    array('udi' => 'R1.x100.001:0001','user' => array('firstName' => 'UserFirstName'))
                );
                $this->expectOutputRegex('/UserFirstName/');
            }
        }
    }

    public function testEventHappenedUserLastName()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('userLastName', $testInfo['tests'])) {
                eventHappened(
                    $event,
                    array('udi' => 'R1.x100.001:0001','user' => array('lastName' => 'UserLastName'))
                );
                $this->expectOutputRegex('/UserLastName/');
            }
        }
    }

    public function testEventHappenedUserSingleRC()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('userRCList', $testInfo['tests'])) {
                foreach (array(array('userId' => 'user1'),array('risUserId' => 1)) as $data) {
                    print "\n\nTesting event: $event\n\nwith data:\n\n";
                    foreach ($data as $key => $val) {
                        print "  $key => $val\n";
                    }
                    print "\n\n";
                    eventHappened($event, $data);
                    $this->expectOutputRegex('/is a member of Sample Project 1/');
                }
            }
        }
    }

    public function testEventHappenedUserMultipleRC()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('userRCList', $testInfo['tests'])) {
                foreach (array(array('userId' => 'user2'),array('risUserId' => 2)) as $data) {
                    print "\n\nTesting event: $event\n\nwith data:\n\n";
                    foreach ($data as $key => $val) {
                        print "  $key => $val\n";
                    }
                    print "\n\n";
                    eventHappened($event, $data);
                    $this->expectOutputRegex('/is a member of Sample Project 2, Sample Project 3/');
                }
            }
        }
    }

    public function testEventHappenedUDI()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('udi', $testInfo['tests'])) {
                eventHappened($event, array('udi' => 'R1.x100.001:0001'));
                $this->expectOutputRegex('/R1\.x100\.001:0001/');
            }
        }
    }

    public function testEventHappenedUDIRC()
    {
        foreach ($this->events as $event => $testInfo) {
            if (in_array('udiRC', $testInfo['tests'])) {
                eventHappened($event, array('udi' => 'R1.x100.001:0001'));
                $this->expectOutputRegex('/(is associated with|for) Sample Project 1./');
            }
        }
    }
}
