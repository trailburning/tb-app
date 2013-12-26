<?php

namespace TBAPI\exceptions;

class ApiException extends \Exception 
{
    private $HTTPErrorCode;
    private $errorCode;
    private $debugmsg;
    
    public function __construct($message, $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return '{"message" : "'.$this->message.'"}';
    }
}
