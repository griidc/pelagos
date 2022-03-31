<?php

namespace App\Controller\Admin;

use App\Entity\InformationProductTypeDescriptor;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Easy Admin Main Dashboard Class.
 */
class DashboardController extends AbstractDashboardController
{
    /**
     * Main dashboard page.
     *
     * @Route("/admin", name="admin")
     *
     * @IsGranted("ROLE_DATA_REPOSITORY_MANAGER")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('Admin/index.html.twig');
    }

    /**
     * Dashboard configuration function.
     *
     * @return Dashboard
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Pelagos');
    }

    /**
     * Function to configure menu items.
     *
     * @return iterable
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Admin Links', 'fa fa-home');
        yield MenuItem::linkToCrud('IP Product Descriptor', 'fas fa-list', InformationProductTypeDescriptor::class);
    }
}
