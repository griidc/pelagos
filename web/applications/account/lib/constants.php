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
                                    'attrs' => array('r','u','a')
                                ),
                                'sn' => array(
                                    'name' => 'Last Name',
                                    'attrs' => array('r','u','a')
                                ),
                                'cn' => array(
                                    'name' => 'Display Name',
                                    'attrs' => array('r','u','a')
                                ),
                                'mail' => array(
                                    'name' => 'Email',
                                    'attrs' => array('r','a')
                                ),
                                'title' => array(
                                    'name' => 'Title',
                                    'attrs' => array('u','a')
                                ),
                                'telephoneNumber' => array(
                                    'name' => 'Phone',
                                    'attrs' => array('u','a')
                                ),
                                'o' => array(
                                    'name' => 'Organization',
                                    'attrs' => array('u','a')
                                ),
                                'userPassword' => array(
                                    'name' => 'Password',
                                    'attrs' => array('r','u')
                                )
                            );

$GLOBALS['POSIX_ACCOUNT_FIELDS'] = array('uidNumber','gidNumber','gecos','homeDirectory','loginShell');

$GLOBALS['NAME_ATTRS'] = array('groupOfNames'=>'cn','organizationalUnit'=>'ou','posixGroup'=>'cn');

?>
