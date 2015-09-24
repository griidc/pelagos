<?php

namespace Pelagos;

/**
 * Class to represent and HTTP status response.
 */
class HTTPStatus implements \JsonSerializable
{
    /**
     * The HTTP response code.
     *
     * @var integer $code
     */
    protected $code;

    /**
     * A message to send with the response.
     *
     * @var string $message
     */
    protected $message;

    /**
     * A data package to send with the response.
     *
     * @var mixed $data
     */
    protected $data;

    /**
     * Constructor for HTTPStatus.
     *
     * @param integer $code    The HTTP response code.
     * @param string  $message A message to send with the response.
     * @param mixed   $data    A data package to send with the response.
     */
    public function __construct($code, $message = null, $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
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
     * Getter for $data.
     *
     * @return mixed The data package to send with the response for this HTTPStatus.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Serializer to be called when json_encode is called on this object.
     *
     * This is required to implement \JsonSerializable.
     *
     * @return array Array representing this object's properties.
     */
    public function jsonSerialize()
    {
        $arrayToSerialize = array(
            'code' => $this->code,
            'message' => $this->message,
        );
        if (isset($this->data)) {
            $arrayToSerialize['data'] = $this->data;
        }
        return $arrayToSerialize;
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
