<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\DataRepository;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Entity\NationalDataCenter;
use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\Funder;
use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use App\Entity\LogActionItem;
use App\Entity\Person;
use App\Entity\ProductTypeDescriptor;
use App\Entity\ResearchGroup;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Easy Admin Main Dashboard Class.
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class DashboardController extends AbstractDashboardController
{
    /**
     * Main dashboard page.
     */
    #[Route(path: '/admin', name: 'admin')]
    #[\Override]
    public function index(): Response
    {
        return $this->render('Admin/index.html.twig');
    }

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Pelagos');
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Admin Links', 'fa fa-link');

        yield MenuItem::section('Viewers');
        yield MenuItem::linkToCrud('Logged Actions', 'fas fa-search', LogActionItem::class);
        yield MenuItem::linkToCrud('DIFs', 'fas fa-search', DIF::class);
        yield MenuItem::linkToCrud('Datasets', 'fas fa-search', Dataset::class);
        yield MenuItem::linkToCrud('Dataset Submissions', 'fas fa-search', DatasetSubmission::class);

        yield MenuItem::section('Editors');
        yield MenuItem::linkToCrud('IP Product Descriptor', 'fas fa-list-alt', ProductTypeDescriptor::class);
        yield MenuItem::linkToCrud('IP Digital Resource Descriptor', 'fas fa-list-alt', DigitalResourceTypeDescriptor::class);

        yield MenuItem::section('PODS');
        yield MenuItem::linkToCrud('Funding Organization', 'fas fa-list-alt', FundingOrganization::class);
        yield MenuItem::linkToCrud('Funding Cycle', 'fas fa-list-alt', FundingCycle::class);
        yield MenuItem::linkToCrud('Research Groups', 'fas fa-list-alt', ResearchGroup::class);
        yield MenuItem::linkToCrud('Person', 'fas fa-list-alt', Person::class);
        yield MenuItem::linkToCrud('Funders', 'fas fa-list-alt', Funder::class);
        yield MenuItem::linkToCrud('National Data Center', 'fas fa-list-alt', NationalDataCenter::class);
        yield MenuItem::linkToCrud('Data Respository', 'fas fa-list-alt', DataRepository::class);


        yield MenuItem::section('Lists');
        yield MenuItem::linkToUrl('Information Products', 'fas fa-list-alt', $this->generateUrl('pelagos_app_ui_information_products'));
        yield MenuItem::section('Create New');
        yield MenuItem::linkToUrl('Information Product', 'fas fa-plus', $this->generateUrl('pelagos_app_ui_information_product'));
        yield MenuItem::section('');
        yield MenuItem::linkToUrl('Homepage', 'fas fa-home', $this->generateUrl('pelagos_homepage'));
    }
}
