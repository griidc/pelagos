<?php

namespace Pelagos;

class HTTPStatus implements \JsonSerializable
{
    public $code;
    public $message;

    public function __construct($code, $message = null)
    {
        $this->code = $code;
        $this->message = $message;
    }

    public function jsonSerialize()
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }

    public function asJSON()
    {
        return json_encode($this);
    }
}
