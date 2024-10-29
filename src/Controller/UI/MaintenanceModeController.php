<?php

namespace App\Controller\UI;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Util\MaintenanceMode;

/**
 * The DIF controller for the Pelagos UI App Bundle.
 */
class MaintenanceModeController extends AbstractController
{
    /**
     * The default action for Maintenance Mode.
     *
     * @param Request         $request         The Symfony request object.
     * @param MaintenanceMode $maintenanceMode Maintenance mode utility class object.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/maintenancemode', name: 'pelagos_app_ui_maintenancemode_default')]
    public function defaultAction(Request $request, MaintenanceMode $maintenanceMode)
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $bannerMode = $request->request->get('bannermode');

        if ($bannerMode === 'activate') {
            $bannerText = $request->request->get('bannertext');
            $bannerColor = $request->request->get('bannercolor');

            $maintenanceMode->activateMaintenanceMode($bannerText, $bannerColor);
        }

        if ($bannerMode === 'deactivate') {
            $maintenanceMode->deactivateMaintenanceMode();
        }

        return $this->render(
            'MaintenanceMode/maintenanceMode.html.twig'
        );
    }
}
