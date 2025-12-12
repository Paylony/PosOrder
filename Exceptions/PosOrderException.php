<?php

namespace YourVendor\PosOrder\Exceptions;

use Exception;

class PosOrderException extends Exception
{
    protected $response;
    protected $statusCode;

    public function __construct($message = "", $code = 0, $response = null, $statusCode = null)
    {
        parent::__construct($message, $code);
        $this->response = $response;
        $this->statusCode = $statusCode;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}