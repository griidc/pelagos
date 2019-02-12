<?php

namespace Symfony\Component\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * The Kernel is the heart of the Symfony system.
 *
 * It manages an environment made of bundles.
 */
class AppKernel extends Kernel
{
    /**
     * Returns an array of bundles to register.
     *
     * @return BundleInterface[] An array of bundle instances.
     */
    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new \Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
            new \SimpleThings\EntityAudit\SimpleThingsEntityAuditBundle(),
            new \FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new \OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new \Phobetor\RabbitMqSupervisorBundle\RabbitMqSupervisorBundle(),
            new \FOS\ElasticaBundle\FOSElasticaBundle(),
            new \Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new \Pelagos\Bundle\LegacyBundle\PelagosLegacyBundle(),
            new \Pelagos\Bundle\AppBundle\PelagosAppBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test', 'drupal_dev'), true)) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    /**
     * Gets the application root dir.
     *
     * @return string The application root dir.
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * Gets the cache directory.
     *
     * @return string The cache directory.
     */
    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory.
     */
    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance.
     *
     * @return void
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
        $localConfigFile = $this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.local.yml';
        if (file_exists($localConfigFile)) {
            $loader->load($localConfigFile);
        }
    }
}
