<?php
// @codingStandardsIgnoreFile

function cmpRegisteredIdentified($a, $b)
{
    if ($a['registered_count'] == $b['registered_count']) {
        if ($a['identified_count'] == $b['identified_count']) {
            if (array_key_exists('PI', $a)) {
                return strcmp($a['PI']['LastName'], $b['PI']['LastName']);
            }
            return 0;
        }
        return ($a['identified_count'] > $b['identified_count']) ? -1 : 1;
    }
    return ($a['registered_count'] > $b['registered_count']) ? -1 : 1;
}
