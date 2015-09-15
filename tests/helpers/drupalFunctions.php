<?php
// @codingStandardsIgnoreFile

/**
  * Mock the drupal_add_js function to just print the URL.
  * The URL will be preceded with "drupal_add_js: " and be terminated with a newline.
  *
  * @param $url string The URL to the javascript file.
  */
function drupal_add_js($url)
{
    print "drupal_add_js: $url\n";
}

/**
  * Mock the drupal_add_css function to just print the URL.
  * The URL will be preceded with "drupal_add_css: " and be terminated with a newline.
  *
  * @param $url string The URL to the CSS file.
  */
function drupal_add_css($url)
{
    print "drupal_add_css: $url\n";
}

/**
  * Mock the drupal_add_libray function to just print the module and library name.
  * The URL will be preceded with "drupal_add_library: " and be terminated with a newline.
  * The module and libray name will be separated with ::
  *
  * @param $module string The name of the module that registered the library.
  * @param $name string The name of the library to add.
  */
function drupal_add_library($module, $name)
{
    print "drupal_add_library: $module::$name\n";
}

/**
  * Mock the drupal_exit function to just print drupal_exit followed by a newline.
  */
function drupal_exit()
{
    print "drupal_exit\n";
}
