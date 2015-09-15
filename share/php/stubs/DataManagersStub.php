<?php
// @codingStandardsIgnoreFile

function getDMsFromUser($userId)
{
    switch($userId) {
        case 'user1':
            return array(
                array(
                    'id' => 10,
                    'firstName' => 'Data',
                    'lastName' => 'Manager 1',
                    'email' => 'dm1@somewhere.edu',
                    'projectId' => 100
                )
            );
        case 'user2':
            return array(
                array(
                    'id' => 20,
                    'firstName' => 'Data',
                    'lastName' => 'Manager 2',
                    'email' => 'dm2@somewhere.edu',
                    'projectId' => 200
                ),
                array(
                    'id' => 30,
                    'firstName' => 'Data',
                    'lastName' => 'Manager 3',
                    'email' => 'dm3@somewhere.edu',
                    'projectId' => 300
                )
            );
    }
    return array();
}

function getDMsFromUDI($UDI)
{
    switch ($UDI) {
        case 'R1.x100.001:0001':
            return array(
                array(
                    'id' => 10,
                    'firstName' => 'Data',
                    'lastName' => 'Manager 1',
                    'email' => 'dm1@somewhere.edu',
                    'projectId' => 100
                )
            );
        case 'R1.x200.002:0001':
            return array(
                array(
                    'id' => 20,
                    'firstName' => 'Data',
                    'lastName' => 'Manager 2',
                    'email' => 'dm2@somewhere.edu',
                    'projectId' => 200
                )
            );
        case 'R1.x300.003:0001':
            return array(
                array(
                    'id' => 30,
                    'firstName' => 'Data',
                    'lastName' => 'Manager 3',
                    'email' => 'dm3@somewhere.edu',
                    'projectId' => 300
                )
            );
    }
    return array();
}
