<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Repository\DatasetRepository;
use App\Util\PersonUtil;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/userdashboard', routeName: 'user_dashboard')]
class UserController extends AbstractDashboardController
{
    private ?Person $person = null;

    public function __construct(private DatasetRepository $datasetRepository)
    {

    }

    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');

        $this->person = PersonUtil::getPersonFromUser($this->getUser());

        $datasets = $this->datasetRepository->findBy(['creator' => $this->person]);

        $researchGroups = new ArrayCollection();
        foreach ($datasets as $dataset) {
            $researchGroup = $dataset->getResearchGroup();
            if (!$researchGroups->contains($researchGroup)) {
                $researchGroups->add($researchGroup);
            }
        }

        $researchGroups = $researchGroups->toArray();

        return $this->render('Admin/user-dashboard.html.twig', [
            'person' => $this->person,
            'researchGroups' => $researchGroups,
        ]);

        // return $this->render('Ad/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Pelagos');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('My Datasets', 'fa fa-table');


        yield MenuItem::section('Submit Data');
        yield MenuItem::linkToUrl('Submit a DIF', 'fas fa-pen-to-square', $this->generateUrl('pelagos_app_ui_dif_default'));
        yield MenuItem::linkToUrl('Submit a Submission', 'fas fa-pen-to-square', $this->generateUrl('pelagos_app_ui_datasetsubmission_default'));

        yield MenuItem::section('My Organization');
        yield MenuItem::linkToUrl(
            'My Research Groups',
            'fas fa-sitemap',
            $this->generateUrl('pelagos_app_ui_researchgroup_about',
                [
                    'researchGroup' => $this->person->getResearchGroups()[0]->getId()
                ]
            ));

        yield MenuItem::section('My Datasets');
        yield MenuItem::linkToUrl('Dataset Permissions', 'fas fa-key', '#');

        yield MenuItem::section('');
        yield MenuItem::linkToUrl('Homepage', 'fas fa-home', $this->generateUrl('pelagos_homepage'));
    }
}
