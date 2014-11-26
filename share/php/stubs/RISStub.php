<?php

function getProjectDetails($dbh, $filters = array())
{
    return array(
        array(
            'ID' => 1,
            'Title' => 'Sample Project',
            'Abstract' => 'This project is very abstract.',
            'StartDate' => '2010-01-01',
            'EndDate' => '2020-12-31',
            'Location' => 'Everywhere and Nowhere',
            'Fund_Src' => 7,
            'Fund_Abbr' => 'RFP-I',
            'Fund_Name' => 'Year 2-4 Consortia Grants (RFP-I)'
        )
    );
}

function getDMsFromRC($DBH, $RC)
{
    switch ($RC) {
        case 132:
            return array(
                array(
                    'ID' => 2818,
                    'FirstName' => 'Brian',
                    'LastName' => 'Dixon',
                    'Email' => 'dixonb@uga.edu'
                )
            );
        case 134:
            return array(
                array(
                    'ID' => 778,
                    'FirstName' => 'Bruce',
                    'LastName' => 'Lipphardt',
                    'Email' => 'brucel@udel.edu'
                )
            );
        case 135:
            return array(
                array(
                    'ID' => 420,
                    'FirstName' => 'Todd',
                    'LastName' => 'Chavez',
                    'Email' => 'tchavez@usf.edu'
                )
            );
        case 137:
            return array(
                array(
                    'ID' => 573,
                    'FirstName' => 'Matthew',
                    'LastName' => 'Howard',
                    'Email' => 'mkhoward@tamu.edu'
                )
            );
        case 138:
            return array(
                array(
                    'ID' => 943,
                    'FirstName' => 'Shawn',
                    'LastName' => 'Smith',
                    'Email' => 'smith@coaps.fsu.edu'
                )
            );
        case 139:
            return array(
                array(
                    'ID' => 1544,
                    'FirstName' => 'Jason',
                    'LastName' => 'Weick',
                    'Email' => 'jweick@lumcon.edu'
                )
            );
        case 140:
            return array(
                array(
                    'ID' => 951,
                    'FirstName' => 'Jian',
                    'LastName' => 'Sheng',
                    'Email' => 'jian.sheng@ttu.edu'
                )
            );
        case 141:
            return array(
                array(
                    'ID' => 467,
                    'FirstName' => 'Vijay',
                    'LastName' => 'John',
                    'Email' => 'vj@tulane.edu'
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
