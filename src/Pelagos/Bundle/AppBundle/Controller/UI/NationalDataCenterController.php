<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Form\NationalDataCenterType;
use Pelagos\Entity\NationalDataCenter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The National Data Center Controller class.
 *
 */
class NationalDataCenterController extends UIController
{
    /**
     * The default action for National Data center.
     *
     * @param string $id The id of the national data center.
     *
     * @Route("/national-data-center/{id}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $entityHandler = $this->get('pelagos.entity.handler');

        if (!empty($id)) {
            $nationalDataCenter = $entityHandler->get('NationalDataCenter', $id);
            if (!$nationalDataCenter instanceof NationalDataCenter) {
                throw $this->createNotFoundException('The National data center was not found');
            }
        } else {
            $nationalDataCenter = new NationalDataCenter('', '');
        }

        $form = $this->get('form.factory')->createNamed(null, NationalDataCenterType::class, $nationalDataCenter);

        return $this->render('@PelagosApp/template/NationalDataCenter.html.twig', array(
            'NationalDataCenter' => $nationalDataCenter,
            'form' => $form->createView(),
            'entityService' => $this->entityHandler
        ));
    }
}
