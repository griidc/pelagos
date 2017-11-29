<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Symfony\Component\Form\Form;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;
use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionXmlFileType;

use Pelagos\Entity\Account;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;
use Pelagos\Entity\Person;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\PersonDatasetSubmission;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;

use Pelagos\Exception\InvalidMetadataException;

use Pelagos\Util\ISOMetadataExtractorUtil;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 *
 * @Route("/dataset-review")
 */
class DatasetReviewController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * A queue of messages to publish to RabbitMQ.
     *
     * @var array
     */
    protected $messages = array();

    /**
     * The default action for Dataset Review.
     *
     * @throws BadRequestHttpException When xmlUploadForm is submitted without a file.
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
