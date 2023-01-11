<?php

namespace App\Controller\Admin;

use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\ProductTypeDescriptor;
use App\Entity\Funder;
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
        yield MenuItem::linktoDashboard('Admin Links', 'fa fa-link');
        yield MenuItem::section('Editors');
        yield MenuItem::linkToCrud('IP Product Descriptor', 'fas fa-list-alt', ProductTypeDescriptor::class);
        yield MenuItem::linkToCrud('IP Digital Resource Descriptor', 'fas fa-list-alt', DigitalResourceTypeDescriptor::class);
        yield MenuItem::linkToCrud('Funder', 'fas fa-list-alt', Funder::class);
        yield MenuItem::section('Lists');
        yield MenuItem::linkToUrl('Information Products', 'fas fa-list-alt', $this->generateUrl('pelagos_app_ui_information_products'));
        yield MenuItem::section('Create New');
        yield MenuItem::linkToUrl('Information Product', 'fas fa-plus', $this->generateUrl('pelagos_app_ui_information_product'));
        yield MenuItem::section('');
        yield MenuItem::linkToUrl('Homepage', 'fas fa-home', $this->generateUrl('pelagos_homepage'));
    }
}
