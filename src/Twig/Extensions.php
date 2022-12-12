<?php

namespace App\Twig;

use App\Entity\DIF;
use App\Util\MaintenanceMode;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Environment;

/**
 * Custom Twig extensions for Pelagos.
 */
class Extensions extends AbstractExtension
{
    /**
     * The kernel root path.
     *
     * @var string
     */
    private $kernelRootDir;

    /**
     * The maintenance mode service.
     *
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * The Router Interface.
     *
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * The list of routes to have their baseUrl removed.
     *
     * @var array
     */
    protected $excludeRoutes;

    /**
     *  Constructor.
     *
     * @param KernelInterface $kernel          The Symfony kernel.
     * @param MaintenanceMode $maintenanceMode The maintenance mode utility.
     */
    public function __construct(KernelInterface $kernel, MaintenanceMode $maintenanceMode, UrlGeneratorInterface $router, array $excludeRoutes)
    {
        $this->kernelRootDir = $kernel->getProjectDir();
        $this->maintenanceMode = $maintenanceMode;
        $this->router = $router;
        $this->excludeRoutes = $excludeRoutes;
    }

    /**
     * Return the name of this extension set.
     *
     * @return string The name of this extension set.
     */
    public function getName()
    {
        return 'Pelagos Twig Extensions';
    }

    /**
     * Return the custom Twig functions.
     *
     * @return array The custom Twig functions.
     */
    public function getFunctions()
    {
        return array(
            new \Twig\TwigFunction(
                'isMaintenanceMode',
                [$this, 'isMaintenanceMode']
            ),
            new \Twig\TwigFunction(
                'getMaintenanceModeText',
                [$this, 'getMaintenanceModeText']
            ),
            new \Twig\TwigFunction(
                'getMaintenanceModeColor',
                [$this, 'maintenanceModeColor']
            ),
            new \Twig\TwigFunction(
                'vanityurl',
                [$this, 'getVanityUrl']
            ),
            new \Twig\TwigFunction(
                'vanitypath',
                [$this, 'getVanityPath']
            ),
        );
    }

    /**
     * Return a list of filters.
     *
     * @return array A list of Twig filters.
     */
    public function getFilters()
    {
        return array(
            new \Twig\TwigFilter(
                'evaluate',
                array(self::class, 'evaluate'),
                array(
                    'needs_environment' => true,
                    'needs_context' => true,
                    'is_safe' => array(
                        'evaluate' => true,
                    )
                )
            ),
            new \Twig\TwigFilter(
                'submittedDIFs',
                array(self::class, 'submittedDIFs')
            ),
            new \Twig\TwigFilter(
                'transformXml',
                array($this, 'transformXml')
            ),
            new \Twig\TwigFilter(
                'role',
                array(self::class, 'role')
            ),
            new \Twig\TwigFilter(
                'formatBytes',
                array(self::class, 'formatBytes')
            ),
            new \Twig\TwigFilter(
                'formatWebDoi',
                array(self::class, 'formatWebDoi')
            ),
            new \Twig\TwigFilter(
                'maintenanceModeColor',
                [$this, 'maintenanceModeColor']
            ),
            new \Twig\TwigFilter(
                'orTemplateIfNotExists',
                [$this, 'doesTwigFileExist']
            ),
        );
    }

    /**
     * Does the template exist, or else return base template.
     *
     * @param string $file    The file name (or part) template to be used.
     * @param string $default The file name of the default template if $file does not exist.
     *
     * @return string Filename of basepath, or default.
     */
    public function doesTwigFileExist(string $file, string $default = ""): string
    {
        if (empty($file)) {
            return $default;
        }
        $filePath = $this->kernelRootDir . '/templates/' . $file ;
        if (file_exists($filePath)) {
            return $file;
        }
        if (file_exists($filePath . '.twig')) {
            return $file . '.twig';
        }
        if (file_exists($filePath . '.html')) {
            return $file . '.html';
        }
        if (file_exists($filePath . '.html.twig')) {
            return $file . '.html.twig';
        }

        return $default;
    }

    /**
     * Is the system in maintenance mode.
     *
     * @return boolean If in maintenance mode.
     */
    public function isMaintenanceMode(): bool
    {
        return $this->maintenanceMode->isMaintenanceMode();
    }

    /**
     * Gets the maintenance text.
     *
     * @return string|null Returns maintenance mode banner text.
     */
    public function getMaintenanceModeText(): ?string
    {
        return $this->maintenanceMode->getMaintenanceModeText();
    }

    /**
     * Gets maintenance mode color.
     *
     * @param string $color The color text.
     *
     * @return string|null Returns maintenance mode banner color.
     */
    public function maintenanceModeColor(string $color = null): ?string
    {
        $bannerColor = $this->maintenanceMode->getMaintenanceModeColor();

        if (empty($bannerColor)) {
            $bannerColor = $color;
        }

        return $bannerColor;
    }

    /**
     * Evaluate Twig commands in a string.
     *
     * @param Environment $environment The Twig environment.
     * @param array       $context     The Twig context.
     * @param string      $string      The string to evaluate.
     *
     * @return string The evaluated string.
     */
    public static function evaluate(Environment $environment, array $context, string $string)
    {
        $loader = $environment->getLoader();
        $parsed = self::parseString($environment, $context, $string);
        $environment->setLoader($loader);
        return $parsed;
    }

    /**
     * Filter for DIFs in submitted status.
     *
     * @param Collection $datasets A collection of datasets.
     *
     * @return Collection The filtered collection.
     */
    public static function submittedDIFs(Collection $datasets)
    {
        return $datasets->filter(
            function ($dataset) {
                return $dataset->getDif()->getStatus() !== DIF::STATUS_UNSUBMITTED;
            }
        );
    }

    /**
     * Filter Person associations by role name.
     *
     * @param Collection $personAssociations A collection of Person associations.
     * @param string     $roleName           The role name to filter by.
     *
     * @return Collection The filtered collection.
     */
    public static function role(Collection $personAssociations, string $roleName)
    {
        return $personAssociations->filter(
            function ($personAssociation) use ($roleName) {
                return $personAssociation->getRole()->getName() === $roleName;
            }
        );
    }

    /**
     * Parse Twig commands in a string.
     *
     * @param Environment $environment The Twig environment.
     * @param array       $context     The Twig context.
     * @param string      $string      The string to parse.
     *
     * @return string The parsed string.
     */
    protected static function parseString(Environment $environment, array $context, string $string)
    {
        $environment->setLoader(new \Twig\Loader\ArrayLoader());
        $template = $environment->createTemplate($string);
        return $template->render($context);
    }

    /**
     * Transform the xml document with provided xslt.
     *
     * @param string $xml The raw xml string of the to be formated xml.
     * @param string $xsl The filename of the xsl template.
     *
     * @return string The xslt transformed xml.
     */
    public function transformXml(string $xml, string $xsl)
    {
        if ($xml <> '' and $xml != null) {
            $xmlDoc = new \DOMDocument();
            $xmlDoc->loadXML($xml);

            $xpathdoc = new \DOMXpath($xmlDoc);

            // Go through all the leaves.
            foreach ($xpathdoc->query('//*[not(*)]') as $element) {
                if (strlen($element->nodeValue) > 10000) {
                    // Trim values longer than 10000 characters and insert a veritcal ellipsis.
                    $element->nodeValue = substr($element->nodeValue, 0, 9900) . "\n"
                        . json_decode('"\u22EE"') . "\n"
                        . substr($element->nodeValue, -100);
                }
            }

            // XSL template.
            $xslDoc = new \DOMDocument();
            $xslDoc->load($this->kernelRootDir . '/templates/xsl/' . $xsl);

            // The Processor.
            $proc = new \XSLTProcessor();
            $proc->importStylesheet($xslDoc);

            return $proc->transformToXml($xmlDoc);
        }
    }

    /**
     * Format bytes as a human-readable string (base 10).
     *
     * @param mixed   $bytes     The bytes to format.
     * @param integer $precision The the precision to use (default: 2).
     *
     * @return string
     */
    public static function formatBytes($bytes, int $precision = 2, $unit = null): string
    {
        if (empty($bytes)) {
            $bytes = 0;
        }
        $units = array('B','KB','MB','GB','TB');
        for ($e = (count($units) - 1); $e > 0; $e--) {
            $one = pow(1000, $e);
            if (!empty($unit) and $units[$e] == $unit) {
                return round(($bytes / $one), $precision) . ' ' . $units[$e];
            } elseif (empty($unit) and $bytes >= $one) {
                return round(($bytes / $one), $precision) . ' ' . $units[$e];
            }
        }
        return "$bytes $units[0]";
    }

    /**
     * Format a DOI as hyperlink-style, if it exits, otherwise return empty string.
     *
     * @param string $doiString
     * @return string
     */
    public static function formatWebDoi($doiString): string
    {
        return (!empty($doiString)) ? 'https://doi.org/'.$doiString : '';
    }

     /**
     * Generate the path, remove Base URL if it's on the excluded list.
     *
     * @param string  $name           The route name.
     * @param array   $parameters     Any parameters.
     * @param boolean $relative       Generate relative path.
     *
     * @return string The generated Patg.
     */
    public function getVanityPath($name, $parameters = [], $relative = false)
    {
        $referenceType =  $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH;

        return $this->generate($name, $parameters, $referenceType);
    }

    /**
     * Generate the URL, remove Base URL if it's on the excluded list.
     *
     * @param string  $name           The route name.
     * @param array   $parameters     Any parameters.
     * @param boolean $schemeRelative Generate relative URL.
     *
     * @return string The generated URL.
     */
    public function getVanityUrl($name, $parameters = [], $schemeRelative = false)
    {
        $referenceType =  $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL;

        return $this->generate($name, $parameters, $referenceType);
    }

    /**
     * Generate the URL, and remote base url if it's excluded.
     *
     * @param string  $name           The route name.
     * @param array   $parameters     Any URL parameters.
     * @param boolean $schemeRelative Generate relative URL.
     *
     * @return string The generated URL/path.
     */
    private function generate($name, $parameters, $referenceType)
    {
        if (in_array($name, $this->excludeRoutes)) {
            $context = $this->router->getContext();
            $oldBaseUrl = (string) $context->getBaseUrl();
            $context->setBaseUrl('');
            $context = $this->router->setContext($context);
            $generate = $this->router->generate($name, $parameters, $referenceType);
            // Set the baseUrl back in context.
            $context = $this->router->getContext();
            $context->setBaseUrl($oldBaseUrl);
            $context = $this->router->setContext($context);
        } else {
            $generate = $this->router->generate($name, $parameters, $referenceType);
        }

        return $generate;
    }
}
