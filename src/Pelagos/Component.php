<?php

namespace Pelagos;

class Component
{
    private $entityManager;

    public function addJS($js_file)
    {
        drupal_add_js($GLOBALS['pelagos']['component_path'] . "/$js_file", array('type'=>'external'));
    }

    public function addCSS($css_file)
    {
        drupal_add_css($GLOBALS['pelagos']['component_path'] . "/$css_file", array('type'=>'external'));
    }

    public function addLibrary($library)
    {
        drupal_add_library('system', $library);
    }

    public function quit()
    {
        if (function_exists('drupal_exit')) {
            drupal_exit();
        } else {
            exit;
        }
    }

    /**
     * Get the entity manager.
     * This method returns the entity manager (and creates it first if doesn't exist).
     */
    public function getEntityManager()
    {
        if (!isset($this->entityManager)) {
            $this->createEntityManager();
        }
        return $this->entityManager;
    }

    /**
     * Create the entity manager.
     * This method creates the entity manager with the mappings defined in XML
     * and the database configuration loaded from /etc/opt/pelagos/db.ini
     */
    private function createEntityManager()
    {
        // create Doctrine metadata configuration from mappings defined in XML
        $doctrine_config = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration(
            array(__DIR__.'/../../config/doctrine')
        );

        // get database configuration parameters from db.ini and set up Doctrine database configuration array
        $db_config = parse_ini_file('/etc/opt/pelagos/db.ini', true);
        $db_conn_info = $db_config['GOMRI_RW'];
        $doctrine_conn = array(
            'driver'   => 'pdo_pgsql',
            'user'     => $db_conn_info['username'],
            'password' => $db_conn_info['password'],
            'host'     => $db_conn_info['host'],
            'port'     => $db_conn_info['port'],
            'dbname'   => $db_conn_info['dbname'],
        );

        // create the entity manager
        $this->entityManager = \Doctrine\ORM\EntityManager::create($doctrine_conn, $doctrine_config);
    }
}
