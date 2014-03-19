<?php

namespace AW\HmacBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader as Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AW\HmacBundle\Annotations\HMAC as HMAC;
use AW\HmacBundle\Annotations\Validation as Validation;
use AW\HmacBundle\Exceptions\APIException as APIException;

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
     * Kernel controller
     *
     * @param FilterControllerEvent $event Allows filtering of a controller
     *
     * @throws \TOCC\PlatoBundle\Exceptions\APIException
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

        $request = $controller[0]->get('request');
        foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
            if ($annotation instanceof HMAC) {
                
                // If were using hmac and the route is marked
                // as private, lets check to see if the right credentials have
                // been provided
                if ($this->useHmac && !$annotation->isPublic()) {
                    $this->_hmacValidation($annotation, $request);
                }
                
            } else if (strpos(get_class($annotation), 'AW\HmacBundle\Annotations\Validation') === 0) {
                
                // Add in kernal
                $annotation->setKernel($controller[0]->get('kernel'));
                
                // If the annotation object exists in the Validation namespace
                // call the validate function with the supplied data
                $annotation->validate(
                    $this->_getPostData($request)    
                );
            }
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
}