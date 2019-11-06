<?php

namespace App\Twig;

use Doctrine\Common\Collections\Collection;

use App\Entity\DIF;

use App\Util\MaintenanceMode;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Custom Twig extensions for Pelagos.
 */
class Extensions extends \Twig_Extension
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
     *  Constructor.
     *
     * @param KernelInterface $kernel          The Symfony kernel.
     * @param MaintenanceMode $maintenanceMode The maintenance mode utility.
     */
    public function __construct(KernelInterface $kernel, MaintenanceMode $maintenanceMode)
    {
        $this->kernelRootDir = $kernel->getRootDir();
        $this->maintenanceMode = $maintenanceMode;
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
            new \Twig_SimpleFilter(
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
            new \Twig_SimpleFilter(
                'submittedDIFs',
                array(self::class, 'submittedDIFs')
            ),
            new \Twig_SimpleFilter(
                'transformXml',
                array($this, 'transformXml')
            ),
            new \Twig_SimpleFilter(
                'role',
                array(self::class, 'role')
            ),
            new \Twig_SimpleFilter(
                'formatBytes',
                array(self::class, 'formatBytes')
            ),
            new \Twig\TwigFilter(
                'maintenanceModeColor',
                [$this, 'maintenanceModeColor']
            ),
        );
    }

    /**
     * Is the system in maintenance mode.
     *
     * @return boolean If in maintenance mode.
     */
    public function isMaintenanceMode() : bool
    {
        return $this->maintenanceMode->isMaintenanceMode();
    }

    /**
     * Gets the maintenance text.
     *
     * @return string|null Returns maintenance mode banner text.
     */
    public function getMaintenanceModeText() : ? string
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
    public function maintenanceModeColor(string $color = null) : ? string
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
     * @param \Twig_Environment $environment The Twig environment.
     * @param array             $context     The Twig context.
     * @param string            $string      The string to evaluate.
     *
     * @return string The evaluated string.
     */
    public static function evaluate(\Twig_Environment $environment, array $context, string $string)
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
     * @param \Twig_Environment $environment The Twig environment.
     * @param array             $context     The Twig context.
     * @param string            $string      The string to parse.
     *
     * @return string The parsed string.
     */
    protected static function parseString(\Twig_Environment $environment, array $context, string $string)
    {
        $environment->setLoader(new \Twig_Loader_Array());
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
            $xslDoc->load($this->kernelRootDir . '/../templates/xsl/' . $xsl);

            // The Processor.
            $proc = new \XSLTProcessor();
            $proc->importStylesheet($xslDoc);

            return $proc->transformToXml($xmlDoc);
        }
    }

    /**
     * Format bytes as a human-readable string (base 10).
     *
     * @param integer|null $bytes     The bytes to format.
     * @param integer      $precision The the precision to use (default: 2).
     *
     * @return string
     */
    public static function formatBytes(?int $bytes, int $precision = 2) : string
    {
        if (empty($bytes)) {
            $bytes = 0;
        }
        $units = array('B','KB','MB','GB','TB');
        for ($e = (count($units) - 1); $e > 0; $e--) {
            $one = pow(1000, $e);
            if ($bytes >= $one) {
                return round(($bytes / $one), $precision) . ' ' . $units[$e];
            }
        }
        return "$bytes $units[0]";
    }
}
