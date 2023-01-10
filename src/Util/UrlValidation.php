<?php

namespace App\Util;

/**
 * A class for validating the urls.
 */
class UrlValidation
{
    /**
     * Constant for user-agent string.
     */
    const USER_AGENT = 'GRIIDC Link Checker (https://griidc.org)';

    /**
     * The URL validation method.
     *
     * @param string $url The Url that needs to be validated.
     *
     * @return boolean
     */
    public function validateUrl(string $url)
    {
        $ch = curl_init();
        //php://memory is a read-write streams that allow temporary data to be stored in a file-like wrapper.
        $cookies = tempnam('/tmp', 'php://memory');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //check to see if it worked.
        if (200 !== $httpCode) {
            return "Could not get URL, returned HTTP code $httpCode";
        }

        return true;
    }
}
