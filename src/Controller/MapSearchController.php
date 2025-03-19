<?php

namespace App\Controller;

use App\Repository\DatasetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MapSearchController extends AbstractController
{
    #[Route('/map', name: 'app_map_search')]
    public function index(): Response
    {
        return $this->render('MapSearch/index.html.twig');
    }
}
