<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
use App\Entity\Account;
use App\Entity\Person;
use App\Form\PersonType;
use App\Entity\DIF;

/**
 * The Person api controller.
 */
class PersonController extends EntityController
{
    /**
     * Get a count of People.
     *
     * @param Request $request The request object.
     *
     *
     *
     * @Route("/api/people/count", name="pelagos_api_people_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(Person::class, $request);
    }

    /**
     * Validate a value for a property of a Person.
     *
     * @param Request $request The request object.
     *
     *
     *
     * @Route(
     *     "/api/people/validateProperty",
     *     name="pelagos_api_people_validate_property",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyAction(Request $request)
    {
        return $this->validateProperty(PersonType::class, Person::class, $request);
    }

    /**
     * Validate a value for a property of an existing Person.
     *
     * @param integer $id      The id of the existing Person.
     * @param Request $request The request object.
     *
     *
     *
     * @Route(
     *     "/api/people/{id}/validateProperty",
     *     name="pelagos_api_people_validate_property_existing",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return boolean|string True if valid, or a message indicating why the property is invalid.
     */
    public function validatePropertyExistingAction(int $id, Request $request)
    {
        return $this->validateProperty(PersonType::class, Person::class, $request, $id);
    }

    /**
     * Get the distinct values for a property of a Person.
     *
     * @param string $property The property for which the distinct values are being requested.
     *
     *
     *
     * @Route(
     *     "/api/people/getDistinctVals/{property}",
     *     name="pelagos_api_people_get_distinct_vals",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return array The list of distinct values for a property.
     */
    public function getDistinctValsAction(string $property)
    {
        return $this->getDistinctVals(Person::class, $property);
    }

    /**
     * Get a collection of People.
     *
     * @param Request $request The request object.
     *
     *
     *
     * @Route(
     *     "/api/people",
     *     name="pelagos_api_people_get_collection",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return Response
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(Person::class, $request);
    }

    /**
     * Get a single Person for a given id.
     *
     * @param integer $id The id of the Person to return.
     *
     *
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route(
     *     "/api/people/{id}",
     *     name="pelagos_api_people_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Person
     */
    public function getAction(int $id)
    {
        return $this->handleGetOne(Person::class, $id);
    }

    /**
     * Create a new Person from the submitted data.
     *
     * @param Request $request The request object.
     *
     *
     *
     * @Route(
     *     "/api/people",
     *     name="pelagos_api_people_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body, a "created" status code,
     *                  and the location of the new Person in the Location header.
     */
    public function postAction(Request $request)
    {
        $person = $this->handlePost(PersonType::class, Person::class, $request);
        return $this->makeCreatedResponse('pelagos_api_people_get', $person->getId());
    }

    /**
     * Replace a Person with the submitted data.
     *
     * @param integer $id      The id of the Person to replace.
     * @param Request $request The request object.
     *
     *
     *
     * @Route(
     *     "/api/people/{id}",
     *     name="pelagos_api_people_put",
     *     methods={"PUT"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function putAction(int $id, Request $request)
    {
        $this->handleUpdate(PersonType::class, Person::class, $id, $request, 'PUT');
        return $this->makeNoContentResponse();
    }

    /**
     * Update a Person with the submitted data.
     *
     * @param integer $id      The id of the Person to update.
     * @param Request $request The request object.
     *
     *
     *
     * @Route(
     *     "/api/people/{id}",
     *     name="pelagos_api_people_patch",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request)
    {
        $this->handleUpdate(PersonType::class, Person::class, $id, $request, 'PATCH');
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Person.
     *
     * @param integer $id The id of the Person to delete.
     *
     *
     *
     * @throws BadRequestHttpException When the person is not deletable due to
     * association with primary point of contact in DIF.
     * @throws BadRequestHttpException When the person is not deletable due to
     * association with secondary point of contact in DIF.
     *
     * @Route(
     *     "/api/people/{id}",
     *     name="pelagos_api_people_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id)
    {
        $primaryPointOfContactCount = $this->entityHandler->count(DIF::class, array('primaryPointOfContact' => $id));
        $secondaryPointOfContactCount = $this->entityHandler->count(DIF::class, array('secondaryPointOfContact' => $id));
        if ($primaryPointOfContactCount > 0) {
            $errStr = 'This Person is not deletable because there ' . ($primaryPointOfContactCount > 1 ? ' are ' : ' is ') . $primaryPointOfContactCount . ' primary point of contact(s) in DIF';
            throw new BadRequestHttpException($errStr);
        } elseif ($secondaryPointOfContactCount > 0) {
            $errStr = 'This Person is not deletable because there ' . ($secondaryPointOfContactCount > 1 ? ' are ' : ' is ') . $secondaryPointOfContactCount . ' secondary point of contact(s) in DIF';
            throw new BadRequestHttpException($errStr);
        } else {
            $this->handleDelete(Person::class, $id);
        }
        return $this->makeNoContentResponse();
    }


    /**
     * Returns a single person.
     *
     * @param Person $person The id of the Person.
     *
     * @Route(
     *     "/api/person/{id}",
     *     name="pelagos_api_get_person",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     *     )
     *
     * @View()
     *
     * @return Response
     */
    public function getPerson(Person $person): Response
    {
        $personData = array(
            'firstName' => $person->getFirstName(),
            'lastName' => $person->getLastName(),
            'emailAddress' => $person->getEmailAddress(),
            'phoneNumber' => $person->getPhoneNumber(),
            'city' => $person->getCity(),
            'administrativeArea' => $person->getAdministrativeArea(),
            'postalCode' => $person->getPostalCode(),
            'country' => $person->getCountry(),
            'organization' => $person->getOrganization(),
            'position' => $person->getPosition(),
        );

        $account = $person->getAccount();
        if ($account instanceof Account && $account === $this->getUser()) {
            $personData['isPosix'] = $account->isPosix();
            $personData['posixUsername'] = $account->getUsername();
            $personData['isMe'] = true;
        }

        return $this->makeJsonResponse($personData);
    }
}
