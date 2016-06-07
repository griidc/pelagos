<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\Account;
use Pelagos\Bundle\AppBundle\Security\AccountVoter;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * The Account API controller.
 */
class AccountController extends EntityController
{
    /**
     * Get a listing of directories and files in an incoming directory or a sub-directory of it.
     *
     * @param Request $request The request object.
     * @param integer $id      The id of the Account to return the incoming directory for.
     *
     * @throws BadRequestHttpException When self is request but the user is not logged in with an account.
     * @throws AccessDeniedException   When the currnt user does not have permission to browse
     *                                 the incoming directory for the requested account.
     * @throws BadRequestHttpException When the requested account is not a POSIX account.
     * @throws BadRequestHttpException When the requested account does not have a home directory.
     *
     * @ApiDoc(
     *   section = "Account",
     *   parameters = {{"name"="subDirectory", "dataType"="string", "required"=false}},
     *   statusCodes = {
     *     200 = "The incoming directory for the requested account was successfully retrieved.",
     *     400 = "The incoming directory for the requested account could not be retrieved (see message).",
     *     403 = "The authenticated user was not authorized to browse the incoming directory for the requested account.",
     *     404 = "The incoming directory for the requested account was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/{id}/incoming-directory")
     *
     * @Rest\View()
     *
     * @return array The incoming directory.
     */
    public function getIncomingDirectoryAction(Request $request, $id)
    {
        if ('self' == $id) {
            if (!$this->getUser() instanceof Account) {
                throw new BadRequestHttpException('You are not logged in with an Account.');
            }
            $account = $this->getUser();
        } else {
            // Get the specified Account.
            $account = $this->handleGetOne(Account::class, $id);
        }
        // Check if the user has permission to browse its incoming directory.
        if (!$this->isGranted(AccountVoter::CAN_BROWSE_INCOMING_DIRECTORY, $account)) {
            // Throw an exception if they don't.
            throw new AccessDeniedException(
                'You do not have sufficient privileges to browse this user\'s incoming directory.'
            );
        }
        if (!$account->isPosix()) {
            throw new BadRequestHttpException('This account is not a POSIX account');
        }
        if (null === $account->getHomeDirectory()) {
            throw new BadRequestHttpException('This account does not have a home directory');
        }
        $incomingDirectory = $account->getHomeDirectory() . '/incoming';
        return $this->readDirectory(
            $incomingDirectory,
            $request->query->get('subDirectory')
        );
    }

    /**
     * Read the directories and files in a directory and return them in a data structure.
     *
     * @param string $baseDirectory The base directory to start reading from.
     * @param string $subDirectory  A sub-directory of the base directory to read.
     *
     * @throws BadRequestHttpException When the sub-directory requested starts with a dot.
     * @throws NotFoundHttpException   When the directory requested does not exist.
     *
     * @return array
     */
    protected function readDirectory($baseDirectory, $subDirectory = null)
    {
        if (preg_match('/^\./', $subDirectory)) {
            throw new BadRequestHttpException(
                'Cannot read hidden directories or traverse the file system above the incoming directory'
            );
        }

        $directoryData = array(
            'basePath' => $baseDirectory,
            'directories' => array(),
            'files' => array(),
        );

        $directory = $baseDirectory;
        if (null !== $subDirectory) {
            $directory .= "/$subDirectory";
        }

        if (!file_exists($directory)) {
            throw new NotFoundHttpException("The directory $directory does not exist");
        }

        $directories = new Finder();
        $directories->directories()->in($directory)->depth('== 0')->sortByName();

        foreach ($directories as $dir) {
            $directoryData['directories'][] = array(
                'name' => $dir->getBaseName(),
                'path' => str_replace("$baseDirectory/", '', $dir->getRealPath()),
            );
        }

        $files = new Finder();
        $files->files()->in($directory)->depth('== 0')->sortByName();

        date_default_timezone_set('America/Chicago');

        foreach ($files as $file) {
            $directoryData['files'][] = array(
                'name' => $file->getBasename(),
                'path' => str_replace("$baseDirectory/", '', $file->getRealPath()),
                'mtime' => date('Y-m-d g:i A T', $file->getMTime()),
                'size' => $file->getSize(),
            );
        }

        date_default_timezone_set('UTC');

        return $directoryData;
    }

    /**
     * Request a user to be converted to POSIX.
     *
     * @throws BadRequestHttpException In event POSIX cannot be requested.
     * @throws AccessDeniedException   If user isn't logged in or does not have an account.
     *
     * @ApiDoc(
     *   section = "Account",
     *   statusCodes = {
     *     204 = "The user successfully requested to be made into a POSIX user.",
     *     400 = "The user can not be made into a POSIX user (see error message for reason).",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Patch("/self/make-posix")
     *
     * @Rest\View()
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function requestMakePosixAction()
    {
        if (!($this->getUser() instanceof Account)) {
            throw new AccessDeniedException('User is either not logged in or does not have an account');
        }

        // Call utility class to POSIX-enable this Account.
        try {
            $this->get('pelagos.util.posixify')->POSIXifyAccount($this->getUser());
            $this->get('old_sound_rabbit_mq.dataset_submission_producer')->publish($this->getUser()->getId(), 'create_homedir');
        } catch (\Exception $e) {
            throw new BadRequestHttpException(
                'Could not request POSIX conversion on this account.  Reason: '
                . $e->getMessage()
            );
        }

        // Return a no content success response.
        return $this->makeNoContentResponse();
    }
}
