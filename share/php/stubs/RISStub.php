<?php

function getProjectDetails($dbh, $filters = array())
{
    $single_sample_project = array(
        array(
            'ID' => 1,
            'Title' => 'Single Sample Project',
            'Abstract' => 'This project is very abstract.',
            'StartDate' => '2010-01-01',
            'EndDate' => '2020-12-31',
            'Location' => 'Everywhere and Nowhere',
            'Fund_Src' => 7,
            'Fund_Abbr' => 'RFP-I',
            'Fund_Name' => 'Year 2-4 Consortia Grants (RFP-I)'
        )
    );
    $two_sample_projects = array(
        array(
            'ID' => 1,
            'Title' => 'Sample Project 1',
            'Abstract' => 'This project is very abstract.',
            'StartDate' => '2010-01-01',
            'EndDate' => '2020-12-31',
            'Location' => 'Everywhere and Nowhere',
            'Fund_Src' => 7,
            'Fund_Abbr' => 'RFP-I',
            'Fund_Name' => 'Year 2-4 Consortia Grants (RFP-I)'
        ),
        array(
            'ID' => 2,
            'Title' => 'Sample Project 2',
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
            case 87:
                return $two_sample_projects;
            case 400:
                return $single_sample_project;
        }
        return $single_sample_project;
    } else {
        return $single_sample_project;
    }
}

function getDMsFromRC($DBH, $RC)
{
    switch ($RC) {
        case 132:
            return array(
                array(
                    'id' => 2818,
                    'firstName' => 'Brian',
                    'lastName' => 'Dixon',
                    'email' => 'dixonb@uga.edu',
                    'projectId' => 132
                )
            );
        case 134:
            return array(
                array(
                    'id' => 778,
                    'firstName' => 'Bruce',
                    'lastName' => 'Lipphardt',
                    'email' => 'brucel@udel.edu',
                    'projectId' => 134
                )
            );
        case 135:
            return array(
                array(
                    'id' => 420,
                    'firstName' => 'Todd',
                    'lastName' => 'Chavez',
                    'email' => 'tchavez@usf.edu',
                    'projectId' => 135
                )
            );
        case 137:
            return array(
                array(
                    'id' => 573,
                    'firstName' => 'Matthew',
                    'lastName' => 'Howard',
                    'email' => 'mkhoward@tamu.edu',
                    'projectId' => 137
                )
            );
        case 138:
            return array(
                array(
                    'id' => 943,
                    'firstName' => 'Shawn',
                    'lastName' => 'Smith',
                    'email' => 'smith@coaps.fsu.edu',
                    'projectId' => 138
                )
            );
        case 139:
            return array(
                array(
                    'id' => 1544,
                    'firstName' => 'Jason',
                    'lastName' => 'Weick',
                    'email' => 'jweick@lumcon.edu',
                    'projectId' => 139
                )
            );
        case 140:
            return array(
                array(
                    'id' => 951,
                    'firstName' => 'Jian',
                    'lastName' => 'Sheng',
                    'email' => 'jian.sheng@ttu.edu',
                    'projectId' => 140
                )
            );
        case 141:
            return array(
                array(
                    'id' => 467,
                    'firstName' => 'Vijay',
                    'lastName' => 'John',
                    'email' => 'vj@tulane.edu',
                    'projectId' => 141
                )
            );
    }
    return array();
}

function getRCsFromRISUser($DBH, $RIS_user_ID)
{
    switch ($RIS_user_ID) {
        case 87:
            return array('135','138');
        case 400:
            return array('134');
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
