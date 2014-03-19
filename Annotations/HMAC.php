<?php
namespace AW\HmacBundle\Annotations;
use AW\HmacBundle\Exceptions\APIException as APIException;

/**
 * This annotation tells whether a particular endpoint requires 
 * authentication or not
 *
 * @Annotation
 *
 * @category  Annotation
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class HMAC
{
    /**
     * Assigned roles
     * 
     * @var array
     */
    private $_roles = array();
    
    /**
     * Public or not
     * 
     * @var boolean 
     */
    private $_public = true;

    /**
     * Creates a new HMAC object
     *
     * @param array $options Annotation options
     * 
     * @return void
     */
    public function __construct(array $options)
    {
        if (isset($options['roles'])) {
            $this->_roles = explode(',', $options['roles']);
        }
        
        if (isset($options['public'])) {
            $this->_public = $options['public'];
        } else {
            $this->_public = true;
        }
    }

    /**
     * Get an array of roles required by this annotation
     *
     * @access public
     * @return array
     */
    public function getRoles()
    {
        return $this->_roles;
    }

    /**
     * Gets the public flag on the route.
     *
     * @return bool The public flag.
     */
    public function isPublic()
    {
        return $this->_public;
    }

    /**
     * See if a given role is required by this annotaion
     *
     * @param string $role
     *
     * @access public
     * @return boolean
     */
    public function isRole($role)
    {
        return in_array($role, $this->_roles);
    }
    
    /**
     * Role comparision function
     * 
     * @param \AW\HmacBundle\Entity\ApiUser $user ApiUser
     * 
     * @throws APIException
     * 
     * @return boolean
     */
    public function checkRoles(\AW\HmacBundle\Entity\ApiUser $user)
    {
        // Check that the API key specified by the client has the 
        // required roles to access this endpoint
        $rolesUser = $user->getRoles();
        $rolesRoute = $this->getRoles();
        foreach ($rolesRoute as $roleRoute) {
            foreach ($rolesUser as $roleUser) {
                if (strtoupper($roleUser) == strtoupper($roleRoute)) {
                    return;
                }
            }
        }

        throw new APIException(
            'HMAC Failed - not allowed to access this route', 
            -1, 
            403
        );
    }
    
    /**
     * Return the expected parameters for the hmac hash
     *
     * @throws \AW\HmacBundle\Exceptions\APIException
     * 
     * @return array
     */
    public static function getHashParams($request)
    { 
        // Merge params
        $params = array_merge(
            $request->request->all(),
            $request->query->all()
        );

        if (!isset($params['hmacKey']) || !isset($params['hmacHash'])) {
            // HMAC is required, but no details were provided
            throw new APIException(
                'HMAC Failed - hash and/or key parameter missing', 
                -1, 
                403
            );
        }
                    
        // Add route to parameters
        $params['route'] = self::_getFullPath($request->getUri());
                    
        // Add method, i.e. GET, PUT
        $params['method'] = $request->getRealMethod();
        
        // Secret
        $params['secret'] = 'test';
                    
        // Sort params
        ksort($params);
        
        return $params;
    }
    
    

    /**
     * Hashes a piece of data
     *
     * @param array $params The parameters to hash
     *
     * @return string
     */
    public static function hash($params)
    {
        $params = array_map('strval', $params);
        
        return hash('SHA256', json_encode($params), false);
    }
    
    /**
     * Return the full path of the request minus any quest string parameters
     * 
     * @param string $uri Fully qualified uri
     * 
     * @return string
     */
    private static function _getFullPath($uri)
    {
        $uri = explode('?', $uri, 2);
        
        return trim($uri[0], '/');
    }
}