<?php

namespace App\Controller\Api;

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
     * @View()
     *
     * @Route(
     *     "/api/upload",
     *     name="pelagos_api_upload_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     *     )
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
     * @View()
     *
     * @Route(
     *     "/api/upload/{uuid}",
     *     name="pelagos_api_upload_delete",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return array The result of the delete.
     */
    public function deleteAction($uuid)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->container->get('pelagos.handler.upload')->handleDelete($uuid);
    }
}
