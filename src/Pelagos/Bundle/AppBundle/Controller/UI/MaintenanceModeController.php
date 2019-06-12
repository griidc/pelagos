<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Util\MaintenanceMode;

/**
 * The DIF controller for the Pelagos UI App Bundle.
 *
 * @Route("/maintenancemode")
 */
class MaintenanceModeController extends UIController
{
    /**
     * The default action for Maintenance Mode.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $bannerMode = $request->request->get('bannermode');
        
        $maintenanceMode = $this->get('pelagos.util.maintenancemode');
        
        if ($bannerMode === 'activate') {
            $bannerText = $request->request->get('bannertext');
            $bannerColor = $request->request->get('bannercolor');
            
            $maintenanceMode->activateMaintenanceMode($bannerText, $bannerColor);
        }
        
        if ($bannerMode === 'deactivate') {
            $maintenanceMode->deactivateMaintenanceMode();
        }

        return $this->render(
            'PelagosAppBundle:MaintenanceMode:maintenanceMode.html.twig'
        );
    }
}
