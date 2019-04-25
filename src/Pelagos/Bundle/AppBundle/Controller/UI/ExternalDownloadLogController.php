<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\Form\Form;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\ExternalDownloadLogType;

use Pelagos\Entity\Account;
use Pelagos\Entity\Dataset;

/**
 * The end review tool helps to end the review of a dataset submission review.
 *
 * @Route("/external-download-log")
 */
class ExternalDownloadLogController extends UIController
{
    /**
     * The default action for End Review.
     *
     * @param Request $request The Symfony request object.
     *
     * @throws \Exception $exception Exception thrown when Dataset does not exist for the given Udi.
     * @throws \Exception $exception Exception thrown when Person does not exist for the given username.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $form = $this->get('form.factory')->createNamed(
            'externalDownloadLog',
            ExternalDownloadLogType::class
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $this->getFormData($form);
            $udi = substr($formData['udi'], 0, 16);
            try {
                $datasets = $this->entityHandler->getby(Dataset::class, array('udi' => $udi));
                if (!empty($datasets) and $datasets[0] instanceof Dataset) {
                    $dataset = $datasets[0];
                    if ($formData['userType']) {
                        $account = $this->entityHandler
                            ->getBy(Account::class, array('userId' => $formData['username']));
                        if (!empty($account) and $account[0] instanceof Account) {
                            $typeId = $account[0]->getUserId();
                            $type = 'GoMRI';
                        } else {
                            throw new \Exception('userNotFound');
                        }
                    } else {
                        $type = 'Non-GoMRI';
                        $typeId = 'anonymous';
                    }

                    $this->container->get('pelagos.event.log_action_item_event_dispatcher')->dispatch(
                        array(
                            'actionName' => 'File Download',
                            'subjectEntityName' => get_class($dataset),
                            'subjectEntityId' => $dataset->getId(),
                            'payLoad' => array('userType' => $type, 'userId' => $typeId, 'downloadType' => 'external'),
                        ),
                        'file_download'
                    );
                    $this->addToFlashBag($request, $udi, 'downloadLogged');
                } else {
                    throw new \Exception('datasetNotFound');
                }
            } catch (\Exception $exception) {
                $this->addToFlashBag($request, $udi, $exception->getMessage());
            }
        }

        return $this->render(
            'PelagosAppBundle:ExternalDownloadLog:default.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Get the form data from the symfony form.
     *
     * @param Form $form Symfony form object with all the input variables.
     *
     * @return array
     */
    private function getFormData(Form $form): array
    {
        $udi = $form->getData()['udi'];
        $userType = $form->getData()['userType'];
        $username = null;

        if ($userType) {
            $username = $form->getData()['username'];
        }

        return array(
            'udi' => $udi,
            'userType' => $userType,
            'username' => $username
        );
    }

    /**
     * Add error messages to flash bag to show it to the user.
     *
     * @param Request $request      The Symfony request object.
     * @param string  $udi          The UDI entered by the user.
     * @param string  $flashMessage The Flashbag message to be showed to the user.
     *
     * @return void
     */
    private function addToFlashBag(Request $request, $udi, $flashMessage)
    {
        $flashBag = $request->getSession()->getFlashBag();

        $error = [
            'datasetNotFound' => 'No dataset found for UDI: ' . $udi,
            'userNotFound' => 'Username entered is not found in the system.'
        ];

        $success = [
            'downloadLogged' => 'The external download for the dataset ' . $udi . ' has been logged in the system.'
        ];

        switch ($flashMessage) {
            case (array_key_exists($flashMessage, $error)):
                $flashBag->add('error', $error[$flashMessage]);
                break;
            case (array_key_exists($flashMessage, $success)):
                $flashBag->add('success', $success[$flashMessage]);
                break;
        }
    }
}
