<?php

namespace Pelagos\Util;

/**
 * A class for validating the urls.
 */
class UrlValidation
{

    /**
     * The URL validation method.
     *
     * @param string $url The Url that needs to be validated.
     *
     * @throws \Exception When the given url does not return a success.
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

            return "Could not get URL, returned HTTP code:$httpCode";
        }

        return true;
    }
}
