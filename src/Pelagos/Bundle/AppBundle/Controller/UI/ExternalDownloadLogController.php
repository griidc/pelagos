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

            if ($formData['userType']) {
                $account = $this->entityHandler
                    ->getBy(Account::class, array('userId' => $formData['username']));
                if ($account[0]) {
                    $typeId = $formData['username']->getUser()->getUserId();
                    $type = 'GoMRI';
                } else {
                    $type = 'Non-GoMRI';
                    $typeId = 'anonymous';
                }
                $datasets = $this->entityHandler->getby(Dataset::class, array('udi' => trim($formData['udi'])));
                $dataset = $datasets[0];

                $this->container->get('pelagos.event.log_action_item_event_dispatcher')->dispatch(
                    array(
                        'actionName' => 'File Download',
                        'subjectEntityName' => get_class($dataset),
                        'subjectEntityId' => $dataset->getId(),
                        'payLoad' => array('userType' => $type, 'userId' => $typeId, 'downloadType' => 'external'),
                    ),
                    'file_download'
                );
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
}
