<?php

namespace AW\HmacBundle\Exceptions;

use \Symfony\Component\HttpFoundation\Response;

/**
 * Exception class for the HmacBundle.
 *
 * @category  Exceptions
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class APIException extends \RuntimeException
{
    /**
     * HTTP Status code
     * 
     * @var integer 
     */
    private $httpStatusCode;

    /**
     * Creates a new APIException object
     *
     * @param string $message        A human readable message describing the error
     * @param string $errorCode      The error code associated with this error
     * @param string $httpStatusCode The HTTP status code that should be sent with this error
     * 
     * @return void
     */
    public function __construct($message, $errorCode, $httpStatusCode = 501)
    {
        $this->httpStatusCode = $httpStatusCode;
        parent::__construct($message, $errorCode);
    }

    /**
     * Get the HTTP code associated with this exception
     *
     * @access public
     * 
     * @return integer
     */
    public function getHTTPStatusCode()
    {
        return $this->httpStatusCode;
    }
    
    /**
     * Return the content for the error response
     * 
     * @return array
     */
    public function getErrorContent()
    {
        return array(
            'errorCode' => $this->getCode(),
            'errorDescription' => $this->getMessage(),
            'errorLocation' => sprintf(
                '%s +%d', 
                $this->getFile(), 
                $this->getLine()
            ),
            'errorTrace' => $this->getTrace()
        );
    }
    
    
    /**
     * Return a default json response
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return new \Symfony\Component\HttpFoundation\Response(
            json_encode($this->getErrorContent()), 
            $this->getHTTPStatusCode(), 
            array('Content-Type' => 'application/json')
        );
    }
}
