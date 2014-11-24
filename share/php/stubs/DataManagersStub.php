<?php

function getDMsFromUser($userID)
{
    switch($userID) {
        case 'schen':
            return array(
                array(
                    'ID' => 778,
                    'FirstName' => 'Bruce',
                    'LastName' => 'Lipphardt',
                    'Email' => 'brucel@udel.edu'
                )
            );
        case 'dhastings':
            return array(
                array(
                    'ID' => 420,
                    'FirstName' => 'Todd',
                    'LastName' => 'Chavez',
                    'Email' => 'tchavez@usf.edu'
                ),
                array(
                    'ID' => 943,
                    'FirstName' => 'Shawn',
                    'LastName' => 'Smith',
                    'Email' => 'smith@coaps.fsu.edu'
                )
            );
    }
    return array();
}

function getDMsFromUDI($UDI)
{
    switch ($UDI) {
        case 'R1.x134.115:0002':
            return array(
                array(
                    'ID' => 778,
                    'FirstName' => 'Bruce',
                    'LastName' => 'Lipphardt',
                    'Email' => 'brucel@udel.edu'
                )
            );
        case 'R1.x135.120:0002':
            return array(
                array(
                    'ID' => 420,
                    'FirstName' => 'Todd',
                    'LastName' => 'Chavez',
                    'Email' => 'tchavez@usf.edu'
                )
            );
    }
    return array();
}
