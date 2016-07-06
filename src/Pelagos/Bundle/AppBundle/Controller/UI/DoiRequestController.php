<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use FOS\RestBundle\Util\Codes;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Pelagos\Bundle\AppBundle\Form\DoiRequestType;
use Pelagos\Bundle\AppBundle\Security\DoiRequestVoter;

use Pelagos\Entity\DoiRequest;

/**
 * The DOI Request controller for the Pelagos UI App Bundle.
 *
 * @Route("/doi-request")
 */
class DoiRequestController extends UIController
{
    /**
     * The default action for the DOI Request.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $id      The id of the Doi Request to load.
     *
     * @Route("/{id}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, $id = null)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('PelagosAppBundle:DIF:notLoggedIn.html.twig');
        }

        if (null !== $id) {
            $doiRequest = $this->entityHandler->get(DoiRequest::class, $id);
        } else {
            $doiRequest = new DoiRequest;
        }

        $form = $this->get('form.factory')
        ->createNamed(
            null,
            DoiRequestType::class,
            $doiRequest
        )
        ->add(
            'submit',
            SubmitType::class,
            array(
                'label' => 'Submit',
                'attr'  => array('class' => 'submitButton'),
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $id) {
                $doiRequest = $this->entityHandler->update($doiRequest);
            } else {
                $doiRequest = $this->entityHandler->create($doiRequest);

                $this->container->get('pelagos.event.entity_event_dispatcher')
                ->dispatch($doiRequest, 'doi_requested');
            }
        }

        return $this->render(
            'PelagosAppBundle:DoiRequest:index.html.twig',
            array(
                'form' => $form->createView(),
                'isValid' => $form->isValid(),
                'isSubmitted' => $form->isSubmitted(),
                'doiRequest' => $doiRequest,
            )
        );
    }

    /**
     * The Approve action for the DOI Request.
     *
     * @param string $id The id of the Doi Request to approve.
     *
     * @Route("/{id}/approve")
     *
     * @throws AccessDeniedException   When the authenticated user does not have permission to approve the DIF.
     * @throws BadRequestHttpException When the DOI Request could not be approved.
     *
     * @return Response A Response instance.
     */
    public function approveAction($id)
    {
        // Get the specific DOI Request.
        $doiRequest = $this->entityHandler->get(DoiRequest::class, $id);

        //Check if the user has permission to approve it.
        if (!$this->isGranted(DoiRequestVoter::CAN_APPROVE, $doiRequest)) {
            // Throw an exception if they don't.
            throw new AccessDeniedException(
                'You do not have sufficient privileges to approve this ' . $doiRequest::FRIENDLY_NAME . '.'
            );
        }

        try {
            // Try to approve the DOI Request.
            $doiRequest->approve();
        } catch (\Exception $exception) {
            // Throw an exception if we can't.
            throw new BadRequestHttpException($exception->getMessage());
        }
        $doiRequest = $this->entityHandler->update($doiRequest);

        return new Response(null, Codes::HTTP_NO_CONTENT, array('Content-Type' => 'application/x-empty'));
    }
}
