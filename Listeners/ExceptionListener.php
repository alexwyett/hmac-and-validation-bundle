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
        // Get the called exception
        $exception = $event->getException();

        // If the exception is of type 'APIException', get the statusCode 
        // from the exception, otherwise default to 500 (Internal Server Error)
        if ($exception instanceof APIException) {
            $event->setResponse($exception->getResponse());
        }
    }
}
