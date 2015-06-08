<?php

namespace Pelagos;

/**
 * Class to represent and HTTP status response.
 */
class HTTPStatus implements \JsonSerializable
{
    /** @var int $code The HTTP response code. **/
    protected $code;

    /** @var string $message A message to send with the response. **/
    protected $message;

    /**
     * Constructor for HTTPStatus.
     *
     * @param int $code The HTTP response code.
     * @param string $message A message to send with the response.
     */
    public function __construct($code, $message = null)
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * Getter for $code.
     *
     * @return int The HTTP response code for this HTTPStatus.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Getter for $message.
     *
     * @return int The message to send with the response for this HTTPStatus.
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Serializer to be called when json_encode is called on this object.
     * This is required to implement \JsonSerializable.
     *
     * @return array Array representing this object's properties.
     */
    public function jsonSerialize()
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }

    /**
     * Method to serialize this object as JSON.
     *
     * @return string A JSON string representing this object's properties.
     */
    public function asJSON()
    {
        return json_encode($this);
    }
}
