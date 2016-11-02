<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * The upload api controller.
 */
class UploadController extends EntityController
{
    /**
     * Process a post of a file or a file chunk.
     *
     * @param Request $request The Symfony request object.
     *
     * @Rest\View()
     *
     * @return array The result of the post.
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->container->get('pelagos.handler.upload')->handleUpload($request);
    }

    /**
     * Delete an uploaded file.
     *
     * @param string $uuid The UUID of the file to delete.
     *
     * @Rest\View()
     *
     * @return array The result of the delete.
     */
    public function deleteAction($uuid)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->container->get('pelagos.handler.upload')->handleDelete($uuid);
    }
}
