<?php

namespace App\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class InformationProductController extends AbstractController
{
    /**
     * The information product page.
     *
     * @Route("/information-product", name="pelagos_app_ui_information_product")
     *
     * @return Response A Response instance.
     */
    public function index(): Response
    {
        return $this->render('InformationProduct/index.html.twig');
    }
}
