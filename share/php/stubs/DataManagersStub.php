<?php

function getDMsFromUser($userId)
{
    switch($userId) {
        case 'schen':
            return array(
                array(
                    'id' => 778,
                    'firstName' => 'Bruce',
                    'lastName' => 'Lipphardt',
                    'email' => 'brucel@udel.edu',
                    'projectId' => 134
                )
            );
        case 'dhastings':
            return array(
                array(
                    'id' => 420,
                    'firstName' => 'Todd',
                    'lastName' => 'Chavez',
                    'email' => 'tchavez@usf.edu',
                    'projectId' => 135
                ),
                array(
                    'id' => 943,
                    'firstName' => 'Shawn',
                    'lastName' => 'Smith',
                    'email' => 'smith@coaps.fsu.edu',
                    'projectId' => 138
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
                    'id' => 778,
                    'firstName' => 'Bruce',
                    'lastName' => 'Lipphardt',
                    'email' => 'brucel@udel.edu',
                    'projectId' => 134
                )
            );
        case 'R1.x135.120:0002':
            return array(
                array(
                    'id' => 420,
                    'firstName' => 'Todd',
                    'lastName' => 'Chavez',
                    'email' => 'tchavez@usf.edu',
                    'projectId' => 135
                )
            );
    }
    return array();
}
