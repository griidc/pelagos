<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * The NationalDataCenterController class.
 *
 * @Route("/national-data-center")
 */
class NationalDataCenterController extends UIController
{
    /**
     * The default action for National Data center.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction()
    {
        return $this->render('@PelagosApp/template/NationalDataCenter.html.twig');
    }
}
