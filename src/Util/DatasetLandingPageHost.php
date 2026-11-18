<?php

namespace App\Util;

class DatasetLandingPageHost
{
    public function __construct(private string $host)
    {
    }

    public function getHost(): string
    {
        return $this->host;
    }
}
