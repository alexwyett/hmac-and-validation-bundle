<?php

namespace AW\HmacBundle\Controller;
use AW\HmacBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AW\HmacBundle\Annotations as AWAnnotation;
use AW\HmacBundle\Annotations\HMAC;
use AW\HmacBundle\Annotations\Validation;
use AW\HmacBundle\Annotations\ValidationCollection;

/**
 * ApiKey Crud controller
 *
 * @category  Controller
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class ApiKeyController extends DefaultController
{
    /**
     * Helper route for debugging hmac requests
     * 
     * @Route("/debug")
     * @Method({"GET", "POST", "PUT", "DELETE", "OPTIONS"})
     * @HMAC(public=true)
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function debugAction()
    {
        $params = AWAnnotation\HMAC::getHashParams(
            $this->getRequest()
        );
        
        // Save hash for later
        $hash = $params['hmacHash'];
        
        // Unset the hash
        unset($params['hmacHash']);
        
        // Formulate the correct hashing array
        $hashParams = $params;
        
        // Add correct hash
        $correctHash = AWAnnotation\HMAC::hash(
            $hashParams
        );
        
        return $this->jsonResponse(
            array(
                'request' => $this->getRequest()->getUri(),
                'method' => $this->getRequest()->getRealMethod(),
                'hash' => $hash,
                'correctHash' => $correctHash,
                'status' => ($hash == $correctHash),
                'hashParams' => $hashParams
            )
        );
    }
    
    /**
     * List ApiUsers function
     * 
     * @Route("/apiuser")
     * @Method("GET")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listApiUsersAction()
    {
        return $this->jsonResponse(
            $this->_getUserService()->getApiUsers()
        );
    }
    
    /**
     * List ApiUsers function
     * 
     * @Route("/apiuser/{apikey}", name="view_apiuser")
     * @Method("GET")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listApiUserAction($apikey)
    {
        return $this->jsonResponse(
            $this->_getUserService()->getApiUser($apikey)
        );
    }
    
    /**
     * Create an api user
     * 
     * @Route("/apiuser")
     * @Method("POST")
     * @HMAC(public=false, roles="ADMIN")
     * @Validation\ValidateString(field="key", maxLength=64)
     * @Validation\ValidateEmail(field="email", maxLength=128)
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createApiUserAction()
    {
        $user = $this->_getUserService()->createUser(
            $this->getFromRequest('key'), 
            $this->getFromRequest('email')
        );
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        
        return $this->createdResponse(
            $this->generateUrl(
                'view_apiuser', 
                array(
                    'apikey' => $user->getApikey()
                )
            )
        );
    }
    
    /**
     * Remove an api user
     * 
     * @Route("/apiuser/{apikey}")
     * @Method("DELETE")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteApiUserAction($apikey)
    {
        $this->_getUserService()->deleteUser($apikey);
        return $this->okResponse();
    }
    
    /**
     * Update an api user
     * 
     * @Route("/apiuser/{apikey}")
     * @Method("PUT")
     * @HMAC(public=false, roles="ADMIN")
     * @Validation\ValidateEmail(field="email", maxLength=128)
     * @Validation\ValidateString(field="secret", maxLength=128)
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateApiUserAction($apikey)
    {
        $this->_getUserService()->updateUser(
            $apikey, 
            array(
                'email' => $this->getFromRequest('email'),
                'secret' => $this->getFromRequest('secret')
            )
        );
        
        return $this->okResponse();
    }
    
    /**
     * Enable an api user
     * 
     * @Route("/apiuser/{apikey}/enable")
     * @Method("POST")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enableApiUserAction($apikey)
    {
        $this->_getUserService()->toggleUser($apikey, true);
        return $this->okResponse();
    }
    
    /**
     * Disable an api user
     * 
     * @Route("/apiuser/{apikey}/disable")
     * @Method("POST")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disableApiUserAction($apikey)
    {
        $this->_getUserService()->toggleUser($apikey, false);
        return $this->okResponse();
    }
    
    /**
     * Add a role to an api user
     * 
     * @Route("/apiuser/{apikey}/role/{role}")
     * @Method("PUT")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addApiUserRoleAction($apikey, $role)
    {
        $this->_getUserService()->setRole($apikey, $role);
        return $this->okResponse();
    }
    
    /**
     * Delete a role to an api user
     * 
     * @Route("/apiuser/{apikey}/role/{role}")
     * @Method("DELETE")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteApiUserRoleAction($apikey, $role)
    {
        $this->_getUserService()->removeRole($apikey, $role);
        return $this->okResponse();
    }
    
    /**
     * Return the user service
     * 
     * @return \AW\HmacBundle\Services\ApiUserService
     */
    private function _getUserService()
    {
        return $this->get('AW_apiuser_service');
    }
}