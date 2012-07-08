<?php
/**
 * @package ImpressPages
 * @copyright   Copyright (C) 2011 ImpressPages LTD.
 * @license see ip_license.html
 */


namespace IpUpdate\Library;


class UpdateException extends \Exception
{
    //error codes
    const UNKNOWN = 0;
    const WRITE_PERMISSION = 1;
    
    private $data;
    
    // Redefine the exception so message isn't optional
    public function __construct($message, $code, $data, \Exception $previous = null) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
        
        $this->data = $data;
    }
    
    public function getValue($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return null;
        }
    }
}