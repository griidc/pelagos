<?php

define('PASTE_PUB_KEY','*** paste your public key here ***');

# key for field codes:
#   r: required
#   u: updatable by user
#   a: updatable by approver

$GLOBALS['PERSON_FIELDS'] = array(
                                'uid' => array(
                                    'name' => 'User ID',
                                    'attrs' => array('r','a')
                                ),
                                'givenName' => array(
                                    'name' => 'First Name',
                                    'attrs' => array('r','u','a'),
                                    'rpis' => array('FirstName')
                                ),
                                'sn' => array(
                                    'name' => 'Last Name',
                                    'attrs' => array('r','u','a'),
                                    'rpis' => array('LastName')
                                ),
                                'cn' => array(
                                    'name' => 'Display Name',
                                    'attrs' => array('r','u','a'),
                                    'rpis' => array('Title','FirstName','MiddleName','LastName','{,}Suffix')
                                ),
                                'mail' => array(
                                    'name' => 'Email',
                                    'attrs' => array('r','a'),
                                    'rpis' => array('Email')
                                ),
                                'title' => array(
                                    'name' => 'Title',
                                    'attrs' => array('u','a'),
                                    'rpis' => array('JobTitle')
                                ),
                                'telephoneNumber' => array(
                                    'name' => 'Phone',
                                    'attrs' => array('u','a'),
                                    'rpis' => array('PhoneNum')
                                ),
                                'o' => array(
                                    'name' => 'Organization',
                                    'attrs' => array('u','a')
                                ),
                                'userPassword' => array(
                                    'name' => 'Password',
                                    'attrs' => array('r','u')
                                ),
                                'employeeNumber' => array(
                                    'name' => 'RPIS ID',
                                    'attrs' => array('a')
                                )
                            );

$GLOBALS['POSIX_ACCOUNT_FIELDS'] = array('uidNumber','gidNumber','gecos','homeDirectory','loginShell');

$GLOBALS['NAME_ATTRS'] = array('groupOfNames'=>'cn','organizationalUnit'=>'ou','posixGroup'=>'cn');

?>
