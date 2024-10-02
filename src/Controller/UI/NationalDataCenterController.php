<?php

namespace App\Controller\UI;

use App\Form\NationalDataCenterType;
use App\Entity\NationalDataCenter;
use App\Handler\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The National Data Center Controller class.
 */
class NationalDataCenterController extends AbstractController
{
    /**
     * The default action for National Data center.
     *
     * @param EntityHandler $entityHandler The entity handler.
     * @param integer       $id            The id of the national data center.
     *
     * @throws NotFoundHttpException When National data center was not found.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/national-data-center/{id}', name: 'pelagos_app_ui_nationaldatacenter_default')]
    public function defaultAction(EntityHandler $entityHandler, FormFactoryInterface $formFactory, int $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        if (!empty($id)) {
            $nationalDataCenter = $entityHandler->get(NationalDataCenter::class, $id);
            if (!$nationalDataCenter instanceof NationalDataCenter) {
                throw new NotFoundHttpException('The National data center was not found');
            }
        } else {
            $nationalDataCenter = new NationalDataCenter();
        }

        $form = $formFactory->createNamed('', NationalDataCenterType::class, $nationalDataCenter);

        return $this->render('template/NationalDataCenter.html.twig', array(
            'NationalDataCenter' => $nationalDataCenter,
            'form' => $form->createView()
        ));
    }
}
