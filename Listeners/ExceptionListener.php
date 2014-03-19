<?php

namespace AW\HmacBundle\Listeners;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AW\HmacBundle\Exceptions\APIException;
use AW\HmacBundle\Responses\ErrorResponse;

/**
 * ApiKey Crud controller
 *
 * @category  Listeners
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class ExceptionListener
{
    /**
     * kernel
     *
     * @var mixed
     * @access protected
     */
    protected $kernel;

    /**
     * __construct
     *
     * @param ContainerInterface $kernel
     *
     * @access public
     * @return void
     */
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Listen to kernel exceptions and handle them appropriately by returning 
     * their contents in the response
     * 
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @param Event $event the event that has occurred
     *
     * @return void
     */
    public function onKernelException(Event $event)
    {
        $exception = $event->getException();
        $errorCode = $exception->getCode();
        $errorDescription = $exception->getMessage();

        // If the exception is of type 'APIException', get the statusCode 
        // from the exception, otherwise default to 500 (Internal Server Error)
        if ($exception instanceof APIException) {
            $statusCode = $exception->getHTTPStatusCode();
        } else {
            $statusCode = 500;
        }

        if ($this->kernel->getEnvironment() == 'dev' 
            || $this->kernel->getEnvironment() == 'test'
        ) {
            $errorTrace = $exception->getTrace();
            $errorLocation = sprintf(
                '%s +%d', 
                $exception->getFile(), 
                $exception->getLine()
            );
        } else {
            $errorTrace = null;
            $errorLocation = null;
        }
        
        // Get the hostname from the request
        $request = $event->getRequest();
        $serverName = $request->server->get('SERVER_NAME');
        $url = str_replace(
            '/var/www',
            '',
            $request->server->get('PATH_TRANSLATED')
        );

        $params = array_merge(
            $request->request->all(),
            $request->query->all()
        );
        
        // Generate json for the log file for logstash to consume
        $errorLog = array();
        $errorLog['status'] = $statusCode;
        $errorLog['errorCode'] = $errorCode;
        $errorLog['errorDescription'] = $errorDescription;
        
        if ($errorLocation) {
            $errorLog['errorLocation'] = $errorLocation;
        } else {
            $errorLog['clientip'] = $request->server->get('REMOTE_ADDR');
            $errorLog['server'] = $request->server->get('SERVER_ADDR');
            $errorLog['url'] = $url;
            $errorLog['queryString'] = http_build_query($params);
        }

        // Generate an error response, and send back
        $errorResponse = new ErrorResponse(
            $statusCode, 
            $errorCode, 
            $errorDescription, 
            $errorTrace, 
            $errorLocation
        );
        $event->setResponse($errorResponse);
    }
}
