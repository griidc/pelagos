<?php
/**
 * Created by PhpStorm.
 * User: ppondicherry
 * Date: 1/25/18
 * Time: 3:45 PM
 */

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Form\EndReviewType;
use Pelagos\Bundle\AppBundle\PelagosAppBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The end review tool helps to end the review of a dataset submission review.
 *
 * @Route("/end-review")
 */
class EndReviewController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The default action for End Review.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $form = $this->get('form.factory')->createNamed(
          null,
          EndReviewType::class
        );

        return $this->render('PelagosAppBundle:EndReview:default.html.twig',
            array('form' => $form->createView())
        );

    }
}