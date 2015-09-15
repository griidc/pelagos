<?php
// @codingStandardsIgnoreFile

function getProjectDetails($dbh, $filters = array())
{
    $sample_projects = array(
        100 => array(
            'ID' => 100,
            'Title' => 'Sample Project 1',
            'Abstract' => 'This project is very abstract.',
            'StartDate' => '2010-01-01',
            'EndDate' => '2020-12-31',
            'Location' => 'Everywhere and Nowhere',
            'Fund_Src' => 7,
            'Fund_Abbr' => 'RFP-I',
            'Fund_Name' => 'Year 2-4 Consortia Grants (RFP-I)'
        ),
        200 => array(
            'ID' => 200,
            'Title' => 'Sample Project 2',
            'Abstract' => 'This project is very abstract.',
            'StartDate' => '2010-01-01',
            'EndDate' => '2020-12-31',
            'Location' => 'Everywhere and Nowhere',
            'Fund_Src' => 7,
            'Fund_Abbr' => 'RFP-I',
            'Fund_Name' => 'Year 2-4 Consortia Grants (RFP-I)'
        ),
        300 => array(
            'ID' => 300,
            'Title' => 'Sample Project 3',
            'Abstract' => 'This project is very abstract.',
            'StartDate' => '2010-01-01',
            'EndDate' => '2020-12-31',
            'Location' => 'Everywhere and Nowhere',
            'Fund_Src' => 7,
            'Fund_Abbr' => 'RFP-I',
            'Fund_Name' => 'Year 2-4 Consortia Grants (RFP-I)'
        )
    );
    if (preg_match('/^peopleid=(\d+)/', $filters[0], $matches)) {
        switch ($matches[1]) {
            case 1:
                return array($sample_projects[100]);
            case 2:
                return array($sample_projects[200],$sample_projects[300]);
        }
        return array();
    } elseif (preg_match('/^projectid=(\d+)/', $filters[0], $matches)) {
        return array($sample_projects[$matches[1]]);
    } else {
        return array();
    }
}

function getPeopleDetails($dbh, $filters = array(), $order_by = 'LastName, FirstName')
{
    $sample_people = array(
        1 => array(
            'ID' => 1,
            'Title' => 'Mr.',
            'LastName' => 'User 1',
            'FirstName' => 'Sample',
            'Street1' => '6300 Ocean Dr',
            'Street2' => 'Unit 5869',
            'City' => 'Corpus Christi',
            'State' => 'TX',
            'PostalCode' => '78412',
            'Email' => 'user1@griidc.org',
            'Phone' => '(361) 825-0001',
        ),
        2 => array(
            'ID' => 2,
            'Title' => 'Mr.',
            'LastName' => 'User 2',
            'FirstName' => 'Sample',
            'Street1' => '6300 Ocean Dr',
            'Street2' => 'Unit 5869',
            'City' => 'Corpus Christi',
            'State' => 'TX',
            'PostalCode' => '78412',
            'Email' => 'user2@griidc.org',
            'Phone' => '(361) 826-0002',
        )
    );
    if (preg_match('/^peopleid=(\d+)/', $filters[0], $matches)) {
        if (array_key_exists($matches[1], $sample_people)) {
            return array($sample_people[$matches[1]]);
        } else {
            return array();
        }
    } else {
        return array();
    }
}

function getDMsFromRC($DBH, $RC)
{
    switch ($RC) {
        case 100:
            return array(
                array(
                    'id' => 10,
                    'firstName' => 'Data',
                    'lastName' => 'Manager 1',
                    'email' => 'dm1@somewhere.edu',
                    'projectId' => 1
                )
            );
        case 200:
            return array(
                array(
                    'id' => 20,
                    'firstName' => 'Data',
                    'lastName' => 'Manager 2',
                    'email' => 'dm2@somewhere.edu',
                    'projectId' => 2
                )
            );
        case 300:
            return array(
                array(
                    'id' => 30,
                    'firstName' => 'Data',
                    'lastName' => 'Manager 3',
                    'email' => 'dm3@somewhere.edu',
                    'projectId' => 3
                )
            );
    }
    return array();
}

function getRCsFromRISUser($DBH, $RIS_user_ID)
{
    switch ($RIS_user_ID) {
        case 1:
            return array(100);
        case 2:
            return array(200,300);
    }
    return array();
}

function getDMsFromRISUser($dbh, $risUserId)
{
    $DMs = array();
    foreach (getRCsFromRISUser($dbh, $risUserId) as $RC) {
        $DMs = array_merge($DMs, getDMsFromRC($dbh, $RC));
    }
    return $DMs;
}
