<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 *
 * @Route("/dataset-review")
 */
class DatasetReviewController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The default action for Dataset Review.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction()
    {
        return $this->render(
            'PelagosAppBundle:DatasetReview:index.html.twig'
        );
    }
}
