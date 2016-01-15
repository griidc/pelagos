<?php

namespace Pelagos\Bundle\LegacyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * The default controller for the Pelagos Legacy Bundle.
 */
class DefaultController extends Controller
{
    /**
     * An action for returning static files.
     *
     * @param string      $file     The path to the file (relative to static/).
     * @param string|null $category The component category (e.g. applications, modules, services, etc).
     * @param string|null $target   The component target (e.g. dif, stats, etc).
     *
     * @throws FileNotFoundException When the file can not be found.
     *
     * @return BinaryFileResponse A response object for the file.
     */
    public function staticAction($file, $category = null, $target = null)
    {
        $pelagosRoot = $this->get('kernel')->getRootDir() . '/..';
        if (isset($category) and isset($target)) {
            $fullPath = "$pelagosRoot/web/$category/$target/static/$file";
        } else {
            $fullPath = "$pelagosRoot/web/static/$file";
        }
        if (file_exists($fullPath)) {
            $response = new BinaryFileResponse($fullPath);
            $response->trustXSendfileTypeHeader();
            if (preg_match('/.js$/', $file)) {
                $response->headers->set('Content-Type', 'text/javascript');
            } elseif (preg_match('/.css$/', $file)) {
                $response->headers->set('Content-Type', 'text/css');
            }
            return $response;
        }
        throw new FileNotFoundException($fullPath);
    }

    /**
     * An action for executing Pelagos legacy components.
     *
     * @param Request $request  The Symfony request object.
     * @param string  $category The component category (e.g. applications, modules, services, etc).
     * @param string  $target   The component target (e.g. dif, stats, etc).
     * @param string  $route    Additional route component.
     *
     * @return Response A Response instance.
     */
    public function execAction(Request $request, $category, $target, $route = '')
    {
        $pelagosRoot = $this->get('kernel')->getRootDir() . '/..';
        $targetPath = "$category/$target";

        // Save original environment.
        $originalEnvironment = array();
        $originalEnvironment['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];
        if (array_key_exists('QUERY_STRING', $_SERVER)) {
            $originalEnvironment['QUERY_STRING'] = $_SERVER['QUERY_STRING'];
        }
        $originalEnvironment['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

        if ($this->container->hasParameter('pelagos.prefix')) {
            $basePath = '/' . $this->getParameter('pelagos.prefix') . '/legacy';
        } else {
            $_SERVER['SCRIPT_NAME'] .= '/legacy';
            $basePath = $_SERVER['SCRIPT_NAME'];
        }

        if (false !== strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
            // append target path to SCRIPT_NAME
            $_SERVER['SCRIPT_NAME'] .= "/$targetPath";
        } else {
            // url is being rewritten
            // Modify environment for the target.
            // Fix up REQUEST_URI.
            // Grab extra path information.
            $extraPath = preg_replace("!^$basePath/$targetPath!", '', $_SERVER['REQUEST_URI']);
            // Start rebuilding REQUEST_URI with Drupal base path + target path.
            $_SERVER['REQUEST_URI'] = "$basePath/$targetPath";
            if (isset($extraPath) and !empty($extraPath)) {
                // If there was extra path info, append it.
                $_SERVER['REQUEST_URI'] .= $extraPath;
            }
            // Fix up SCRIPT_NAME.
            $_SERVER['SCRIPT_NAME'] = "$basePath/$targetPath";
            if (array_key_exists('QUERY_STRING', $_SERVER)) {
                // Fix up QUERY_STRING.
                $_SERVER['QUERY_STRING'] = preg_replace('/^q=[^&]+&?/', '', $_SERVER['QUERY_STRING']);
            }
        }

        // Initialize output.
        $output = '';

        // Define Pelagos global.
        $GLOBALS['pelagos'] = array(
            'root' => $pelagosRoot,
            'base_path' => $basePath,
            'base_url' => $request->getUriForPath($basePath),
            'component_path' => "$basePath/$targetPath",
            'component_url' => $request->getUriForPath("$basePath/$targetPath"),
        );

        // Save the current working directory.
        $pelagosOrigCwd = getcwd();
        // Change to the target driectory.
        chdir("$pelagosRoot/web/$category/$target");

        // Prepend Pelagos php include path.
        set_include_path("$pelagosRoot/share/php" . PATH_SEPARATOR . get_include_path());

        $GLOBALS['drupal_add_css'] = array();
        $GLOBALS['drupal_add_js'] = array();

        ob_start();
        require_once 'index.php';
        $output = ob_get_clean();
        chdir($pelagosOrigCwd);

        // Restore the original environment.
        foreach ($originalEnvironment as $key => $val) {
            $_SERVER[$key] = $val;
        }

        return $this->render(
            'PelagosLegacyBundle:Default:index.html.twig',
            array(
                'title' => $GLOBALS['pelagos']['title'],
                'csses' => $GLOBALS['drupal_add_css'],
                'jses' => $GLOBALS['drupal_add_js'],
                'body' => $output
            )
        );
    }
}
