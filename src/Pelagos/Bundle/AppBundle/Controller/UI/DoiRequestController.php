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

        if ($this->isGranted(DoiRequestVoter::CAN_APPROVE, $doiRequest) and
            null !== $id and
            $doiRequest->getStatus() == DoiRequest::STATUS_SUBMITTED
        ) {
            $buttonLabel = 'Approve';
        } else {
            $buttonLabel = 'Submit';
        }

        $form = $this->get('form.factory')
        ->createNamed(
            null,
            DoiRequestType::class,
            $doiRequest
        )
        ->add(
            $buttonLabel,
            SubmitType::class,
            array(
                'label' => $buttonLabel,
                'attr'  => array(
                'class' => 'submitButton',
                'value' => $buttonLabel,
                ),
            )
        );

        $form->handleRequest($request);

        if ($request->request->get('Approve') == 'Approve') {
            $this->approve($doiRequest);

            // $url, $who, $what, $where, $date
            $doi = $this->issueDOI(
                $doiRequest->getUrl(),
                $doiRequest->getResponsibleParty(),
                $doiRequest->getTitle(),
                $doiRequest->getPublisher(),
                $doiRequest->getPublicationDate()->format('Y-m-d')
            );

            $doiRequest->issue($doi);

            $this->container->get('pelagos.event.entity_event_dispatcher')
            ->dispatch($doiRequest, 'doi_issued');
        }

        $alreadyExists = false;
        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $id) {
                $doiRequest = $this->entityHandler->update($doiRequest);
            } else {
                if ($this->alreadyExists($request->get('url'))) {
                    $alreadyExists = true;
                } else {
                    $doiRequest = $this->entityHandler->create($doiRequest);

                    $this->container->get('pelagos.event.entity_event_dispatcher')
                    ->dispatch($doiRequest, 'doi_requested');
                }
            }
        }

        return $this->render(
            'PelagosAppBundle:DoiRequest:index.html.twig',
            array(
                'form' => $form->createView(),
                'isValid' => $form->isValid(),
                'isSubmitted' => $form->isSubmitted(),
                'doiRequest' => $doiRequest,
                'alreadyExists' => $alreadyExists,
            )
        );
    }

    /**
     * Checks to see if the passed URL already has a DOI registered against it.
     *
     * @param string $url The URL possibly already registered in a DOI.
     *
     * @return boolean
     */
    private function alreadyExists($url)
    {
        $url = rtrim($url, '/');
        $existing = $this->entityHandler->getBy(
            DoiRequest::class,
            array(
                'url' => "$url,$url/",
                'status' => DoiRequest::STATUS_ISSUED
            )
        );

        return (count($existing) > 0);
    }

    /**
     * To approve the DOI Request.
     *
     * @param DoiRequest $doiRequest The DOI Request to be approved.
     *
     * @throws AccessDeniedException   When the authenticated user does not have permission to approve the DIF.
     * @throws BadRequestHttpException When the DOI Request could not be approved.
     *
     * @return void
     */
    private function approve(DoiRequest &$doiRequest)
    {
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
    }

    /**
     * This function will change the status to issued, and set the DOI.
     *
     * @param string $url   URL for DOI Request.
     * @param string $who   Creator for DOI Request.
     * @param string $what  Title for DOI Request.
     * @param string $where Publisher for DOI Request.
     * @param string $date  Published Date for DOI Request.
     * @param string $type  Type for DOI Request, by default Dataset.
     *
     * @throws \Exception When there was an error negotiating with EZID.
     *
     * @return string The DOI issued by EZID.
     */
    private function issueDOI($url, $who, $what, $where, $date, $type = 'Dataset')
    {
        $input = '_target:' . $this->escapeSpecialCharacters($url) . "\n";
        $input .= "_profile:dc\n";
        $input .= 'dc.creator:' . $this->escapeSpecialCharacters($who) . "\n";
        $input .= 'dc.title:' . $this->escapeSpecialCharacters($what) . "\n";
        $input .= 'dc.publisher:' . $this->escapeSpecialCharacters($where) . "\n";
        $input .= "dc.date:$date\n";
        $input .= "dc.type:$type";

        $doishoulder = $this->getParameter('doi_api_shoulder');
        $doiusername = $this->getParameter('doi_api_user_name');
        $doipassword = $this->getParameter('doi_api_password');

        utf8_encode($input);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ezid.cdlib.org/shoulder/$doishoulder");
        curl_setopt($ch, CURLOPT_USERPWD, "$doiusername:$doipassword");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array('Content-Type: text/plain; charset=UTF-8','Content-Length: ' . strlen($input))
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //check to see if it worked.
        if (201 != $httpCode) {
            throw new \Exception("ezid failed with:$httpCode");
        }

        $doi = preg_match('/^success: (doi:\S+)/', $output, $matches);

        return $matches[1];
    }

    /**
     * This function escape :%\n\r characters, because these are special with EZID.
     *
     * @param string $input Text that needs to be escaped.
     *
     * @return string The escaped string.
     */
    private function escapeSpecialCharacters($input)
    {
        return preg_replace_callback(
            '/[%:\r\n]/',
            function ($matches) {
                return sprintf('%%%02X', ord($matches[0]));
            },
            $input
        );
    }
}
