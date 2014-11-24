<?php
if(!function_exists('configMerge')) {
    function configMerge($originalConfigArray,$localConfigArray)
    {
        $newConfig = array();
        foreach(array_keys($originalConfigArray) as $key) {
            if(isset($localConfigArray[$key])) {
                $newConfig[$key] = array_merge($originalConfigArray[$key], $localConfigArray[$key]);
            } else {
                $newConfig[$key]=$originalConfigArray[$key];
            }
        }
        return $newConfig;
    }
}
