<?php

namespace AW\HmacBundle\Responses;

use Symfony\Component\HttpFoundation\Response;

/**
 * This class is used to generate a response
 *
 * @category  Listeners
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class ErrorResponse extends Response
{
    /**
     * Creates a new ErrorResponse object
     *
     * @param int    $statusCode       The HTTP response code to send back
     * @param int    $errorCode        The code associated with the error
     * @param string $errorDescription A human readable description of the error
     * @param string $errorTrace       The stack trace from the error
     * @param string $errorLocation    The location of the error
     *
     * @return ErrorResponse
     */
    public function __construct(
        $statusCode, 
        $errorCode, 
        $errorDescription, 
        $errorTrace, 
        $errorLocation
    ) {
        // Build the response
        $content['errorCode'] = $errorCode;
        $content['errorDescription'] = $errorDescription;
        if ($errorTrace != null and $errorLocation != null) {
            $content['errorLocation'] = $errorLocation;
            $content['errorTrace'] = $errorTrace;
        }
        
        // Call the parent constructor
        parent::__construct(
            json_encode($content), 
            $statusCode, 
            array('Content-Type' => 'application/json')
        );
    }
}