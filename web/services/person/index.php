<?php

// currently set by Drupal to local pelagos root
require_once $GLOBALS['pelagos']['root'] . '/bootstrap.php';

$comp = new \Pelagos\Component();

$comp->slim->get(
    '/',
    function () use ($comp) {
        $GLOBALS['pelagos']['title'] = 'Person Webservice';
        $stash = array('pelagos_base_path' => $GLOBALS['pelagos']['base_path']);
        $comp->slim->render("html/index.html");
        return;
    }
);

$comp->slim->map(
    '/:firstName/:lastName/:email(/)',
    function ($firstName, $lastName, $email) use ($comp, $entityManager) {

        global $user;

        $comp->setQuitOnFinalize(true);

        // First name, last name, and email have to exist, otherwise the route
        // would not have been matched

        // validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $comp->setSlimResponseHTTPStatusJSON(
                new \Pelagos\HTTPStatus(
                    400,
                    'Improperly formatted email address'
                )
            );
            return;
        }

        // Check to see that user is logged in.
        // THIS IS AN INSUFFICIENT SECURITY CHECK, THIS WILL
        // HAVE TO BE TIED TO SOME SORT OF ACCESS LIST WHEN
        // RELEASED.
        if (!isset($user->name)) {
            $comp->setSlimResponseHTTPStatusJSON(
                new \Pelagos\HTTPStatus(
                    401,
                    'Login Required to use this feature'
                )
            );
            return;
        }

        $person = new \Pelagos\Entity\Person($firstName, $lastName, $email);

        $entityManager->persist($person);

        try {
            $entityManager->flush();
            $firstName = $person->getFirstName();
            $lastName = $person->getLastName();
            $email = $person->getEmailAddress();
            $id = $person->getId();

            $code = 200;
            $msg = "A person has been successfully created $firstName $lastName ($email) with at ID of $id.";
        } catch (\Exception $error) {
            // Duplicate Error - 23505
            if (preg_match('/SQLSTATE\[23505\]: Unique violation/', $error->getMessage())) {
                $code = 409;
                $msg = 'This record already exists in the database';
            } else {
                $code = 500;
                $msg = "A general database error has occured." . $error->getMessage();
            }
        }

        $comp->setSlimResponseHTTPStatusJSON(
            new \Pelagos\HTTPStatus($code, $msg)
        );

        return;
    }
)->via('PUT', 'GET');


$comp->slim->run();

$comp->finalize();
