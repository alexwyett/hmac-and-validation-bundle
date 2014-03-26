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
class ValidationException extends APIException
{
    /**
     * Field name which threw the exception
     * 
     * @var string
     */
    protected $field = '';
    
    /**
     * Creates a new APIException object
     *
     * @param string  $field          Field name which threw the exception
     * @param string  $message        Error description
     * @param string  $errorCode      The error code associated with this error
     * @param integer $httpStatusCode The HTTP status code that should be sent
     * 
     * @return void
     */
    public function __construct($field, $message, $errorCode, $httpStatusCode = 501)
    {
        $this->field = $field;
        parent::__construct($message, $errorCode, $httpStatusCode);
    }
    
    /**
     * Return the field name
     * 
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
    
    /**
     * Return the content for the exception
     * 
     * @return array
     */
    public function getErrorContent()
    {
        $content = parent::getErrorContent();
        return array_merge(
            array(
                'errorField' => $this->getField()
            ), 
            $content
        );
    }
}
