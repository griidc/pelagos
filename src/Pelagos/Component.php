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
            $this->entityManager = Persistance::createEntityManager();
        }
        return $this->entityManager;
    }
}
