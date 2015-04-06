<?php

namespace Pelagos;

class Citation
{
    private $id;
    private $text;
    private $style;
    private $locale;
    private $timestamp;
    
    public function __construct(
        $id,
        $text = null,
        $style = null,
        $locale = null,
        $timestamp = null
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->style = $style;
        $this->locale = $locale;
        $this->setTimeStamp($timestamp);
    }

    public function setTimeStamp($timestamp)
    {
        if ($timestamp === null) {
            $this->timestamp = new \DateTime();
        } else {
            $this->timestamp = $timestamp;
        }
    }

    public function asJSON()
    {
        return json_encode(
            array(
                'id' => $this->id,
                'text' => $this->text,
                'style' => $this->style,
                'locale' => $this->locale,
                'timestamp' => $this->timestamp->format('c'),
            ),
            JSON_UNESCAPED_SLASHES
        );
    }
}
