<?php

namespace Pelagos\Component;

class PersonService extends \Pelagos\Component
{
    public function giveDescription()
    {
        $GLOBALS['pelagos']['title'] = 'Person Webservice';
        return $this->slim->render("html/index.html");
    }

    public function createPerson(
        \Doctrine\Common\Persistence\ObjectManager $entityManager,
        $firstName,
        $lastName,
        $emailAddress
    ) {
        global $user;

        $this->setQuitOnFinalize(true);

        // mandatory fields
        if (($emailAddress == '' or $emailAddress == null) or
            ($firstName == '' or $firstName == null) or
            ($lastName == '' or $lastName == null)) {
                $this->setSlimResponseHTTPStatusJSON(
                    new \Pelagos\HTTPStatus(
                        400,
                        'one or more mandatory fields are missing (emailAddress, FirstName, LastName)'
                    )
                );
            return;

        }

        // validate email format
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $this->setSlimResponseHTTPStatusJSON(
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
            $this->setSlimResponseHTTPStatusJSON(
                new \Pelagos\HTTPStatus(
                    401,
                    'Login Required to use this feature'
                )
            );
            return;
        }

        $person = new \Pelagos\Entity\Person($firstName, $lastName, $emailAddress);


        try {
            $entityManager->persist($person);
            $entityManager->flush();
            $firstName = $person->getFirstName();
            $lastName = $person->getLastName();
            $emailAddress = $person->getEmailAddress();
            $id = $person->getId();

            $code = 200;
            $msg = "A person has been successfully created $firstName $lastName ($emailAddress) with at ID of $id.";
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
        $this->setSlimResponseHTTPStatusJSON(new \Pelagos\HTTPStatus($code, $msg));
        return;
    }
}
