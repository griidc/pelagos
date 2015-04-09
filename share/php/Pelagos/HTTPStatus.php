<?php

namespace Pelagos;

class HTTPStatus
{
    public $code;
    public $message;

    public function __construct($code, $message = null)
    {
        $this->code = $code;
        $this->message = $message;
    }

    public function asJSON()
    {
        return json_encode(
            array(
                'code' => $this->code,
                'message' => $this->message,
            )
        );
    }
}
