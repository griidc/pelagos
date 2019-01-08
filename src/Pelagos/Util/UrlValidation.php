<?php

namespace Pelagos\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class UrlValidation
{

    /**
     * The URL validation method.
     *
     * @param string $url The Url that needs to be validated.
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function validateUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //check to see if it worked.
        if (200 !== $httpCode) {
            throw new \Exception("url failed with:$httpCode($output)", $httpCode);
        }

        return true;
    }
}