<?php

namespace AW\HmacBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader as Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AW\HmacBundle\Annotations\HMAC as HMAC;
use AW\HmacBundle\Annotations\Validation as Validation;
use AW\HmacBundle\Exceptions\APIException as APIException;
use \JmesPath;
use \JmesPath\Env;

/**
 * Driver for the HMAC annotation
 *
 * @category  Annotation
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class AnnotationDriver
{
    /**
     * Annotation reader
     * 
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;
    
    /**
     * Entity manager
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * HMAC bool setting
     * 
     * @var boolean
     */
    private $useHmac;

    /**
     * Create a new AnnotationDriver object
     *
     * @param \Doctrine\Common\Annotations\Reader $reader          Annotation reader
     * @param \Doctrine\ORM\EntityManager         $entityManager   EM
     * @param boolean                             $useHmac         HMAC Parameter
     * 
     * @return void
     */
    public function __construct($reader, $entityManager, $useHmac = false)
    {
        $this->reader = $reader;
        $this->entityManager = $entityManager;
        $this->useHmac = $useHmac;
    }

    /**
     * Kernel controller.  Handles all requests before a controller function
     * is called.
     *
     * @param FilterControllerEvent $event Allows filtering of a controller
     *
     * @throws \AW\HmacBundle\Exceptions\APIException
     * 
     * @return void
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        // Check that a controller is being used (as well as assigning to
        // $controller variable)
        if (!is_array($controller = $event->getController())) {
            return;
        }
        
        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);
        
        // Make sure that a symfony controller is being used
        if (!($controller[0] instanceof \Symfony\Bundle\FrameworkBundle\Controller\Controller)) {
            return;
        }
        
        $validationAnnotation = $this->_getValidationCollection($method);        
        $validationExceptions = array();

        $request = $controller[0]->get('request');
        foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
            if ($annotation instanceof HMAC) {
                
                // If were using hmac and the route is marked
                // as private, lets check to see if the right credentials have
                // been provided
                if ($this->useHmac && !$annotation->isPublic()) {
                    $this->_hmacValidation($annotation, $request);
                }
                
            } else if ($this->_isValidationAnnotation($annotation)) {
                
                // Add in kernal
                $annotation->setKernel($controller[0]->get('kernel'));
                
                try {
                    // If the annotation object exists in the Validation namespace
                    // call the validate function with the supplied data
                    $annotation->validate(
                        $this->_getPostData($request)    
                    );
                } catch (APIException $ex) {
                    if ($validationAnnotation) {
                        array_push($validationExceptions, $ex);
                    } else {
                        throw $ex;
                    }
                }
            }
        }
        
        // Set the ValidationCollection exceptions
        if (count($validationExceptions) > 0) {
            $validationAnnotation->setExceptions($validationExceptions);
            $validationAnnotation->setController($controller[0]);
            $validationAnnotation->throwException();
        }
    }

    /**
     * Kernel controller.  Handles all responses from a controller.
     *
     * @param FilterResponseEvent $event Allows filtering of controller responses
     * 
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        
    }

    /**
     * Kernel controller.  Handles all responses from a controller.
     *
     * @param GetResponseForControllerResultEvent $event Allows filtering of controller views before response
     * 
     * @return void
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        // Get returned data
        $data = $event->getControllerResult();
        
        // If the controller response is filterable and there is a filter
        // string provided, then filter the $data array
        if ($event->getRequest()->get('_filterable', false)
            && $event->getRequest()->get('filter', false)
            && (is_array($data) || is_object($data))
        ) {
            $data = JmesPath\Env::search(
                $event->getRequest()->get('filter'),
                $data
            );
            
            if ($data === null) {
                throw new APIException('Invalid filter used', -1);
            }
        }
        
        // Check that the _json attribute is setand that the data is the
        // correct format
        if ($event->getRequest()->get('_format', '') === '_json') {
            // Set the response to the event to be json
            $httpStatus = $event->getRequest()->get('_httpstatus', 200);
            $event->setResponse(new JsonResponse($data, $httpStatus));
        }
    }
    
    /**
     * Perform hmac validation
     * 
     * @param \AW\HmacBundle\Annotations\HMAC           $hmac    HMAC Annotation
     * @param \Symfony\Component\HttpFoundation\Request $request Symfony Request
     * 
     * @throws APIException
     * 
     * @return void
     */
    private function _hmacValidation($hmac, $request)
    {
        // Get the parameters for the exception.  This will throw an
        // exception if some of the provided parameters are false
        $params = $hmac->getHashParams($request);

        $user = $this->entityManager->getRepository(
            'AWHmacBundle:ApiUser'
        )->findOneByApikey($params['hmacKey']);

        // Throw exception if no user is found or if disabled
        if (!$user || !$user->isEnabled()) {
            throw new APIException('Unknown API Key', -1, 403);
        }

        // Add secret to parameters for hashing
        $params['secret'] = $user->getApiSecret();

        // This is a private route, so HMAC is requred.  We'll test this against
        // our generated hash later
        $hash = $params['hmacHash'];

        // Remove hash from params - we'll regenerate this again
        unset($params['hmacHash']);

        // Calc hash
        $_hash = $hmac->hash($params);

        // Ensure the hash we calculated matches the 
        // hash sent by the client
        if ($_hash != $hash) {
            throw new APIException(
                'HMAC Failed - hash mismatch', 
                -1, 
                403
            );
        }

        // Check the user is valid (compares roles)
        $hmac->checkRoles($user);
    }


    /**
     * Return all posted/query parameters in one array.  
     * 
     * @see http://silex.sensiolabs.org/doc/cookbook/json_request_body.html
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     * 
     * @return array|object
     */
    private function _getPostData($request)
    {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            return json_decode($request->getContent());
        } else {
            return array_merge(
                $request->query->all(), 
                $request->request->all()
            );
        }
    }
    
    /**
     * Return true if annotation object is part of the validation namespace
     * 
     * @param object $annotation Annotation instance
     * 
     * @return boolean
     */
    private function _isValidationAnnotation($annotation)
    {
        return (strpos(
            get_class($annotation), 
            'AW\HmacBundle\Annotations\Validation\\'
        ) === 0);
    }
    
    /**
     * Return the validation collection annotation if set
     * 
     * @param string $method Controller method
     * 
     * @return AW\HmacBundle\Annotations\ValidationCollection|boolean
     */
    private function _getValidationCollection($method)
    {
        $array = array_filter(
            $this->reader->getMethodAnnotations($method), 
            function($annotation) {
                return (strpos(
                    get_class($annotation), 
                    'AW\HmacBundle\Annotations\ValidationCollection'
                ) === 0);
            }
        );
        
        if (count($array) == 1) {
            return array_shift($array);
        } else {
            return false;
        }
    }
}