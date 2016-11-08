<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A controller to handle redirects.
 */
class RedirectingController extends Controller
{
    /**
     * Redirect URLs ending in a trailaing slash to same URL with a trailing slash.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/{url}", name="remove_trailing_slash", requirements={"url" = ".*\/$"}, methods={"GET"})
     *
     * @return Response
     */
    public function removeTrailingSlashAction(Request $request)
    {
        return $this->redirect(
            str_replace(
                $request->getPathInfo(),
                rtrim($request->getPathInfo(), ' /'),
                $request->getRequestUri()
            ),
            301
        );
    }
}
