<?php

namespace AW\HmacBundle\Controller;
use AW\HmacBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
class UserController extends DefaultController
{
    /**
     * List Users function
     * 
     * @Route("/user", name="view_users", defaults={"_format" = "_json", "_filterable" = true})
     * @Method("GET")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listUsersAction()
    {
        return $this->_getUserService()->getUsers();
    }
    
    /**
     * List User function
     * 
     * @param integer $userid User Id
     * 
     * @Route("/user/{userid}", name="view_user", defaults={"_format" = "_json"})
     * @Method("GET")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listUserAction($userid)
    {
        return $this->_getUserService()->getUserById($userid)->toArray();
    }
    
    /**
     * Authenticate user action
     * 
     * @Route("/user/authenticate", name="authenticate_user")
     * @Method("POST")
     * @HMAC(public=false, roles="ADMIN")
     * @Validation\ValidateString(field="username", maxLength=64)
     * @Validation\ValidateString(field="password", maxLength=128)
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authenticateAction()
    {
        $user = $this->_getUserService()->getUserByLogin(
            $this->getFromRequest('username'),
            $this->getFromRequest('password')
        );
        if ($user) {
            return $this->okResponse();
        } else {
            throw new \AW\HmacBundle\Exceptions\APIException(
                'Failed to authenticate user',
                -1,
                404
            );
        }
    }
    
    /**
     * Create a user
     * 
     * @Route("/user", name="create_user")
     * @Method("POST")
     * @HMAC(public=false, roles="ADMIN")
     * @Validation\ValidateString(field="username", maxLength=64)
     * @Validation\ValidateEmail(field="email", maxLength=64)
     * @Validation\ValidateString(field="password", maxLength=128)
     * @Validation\ValidateNumber(field="group")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createUserAction()
    {
        $user = $this->_getUserService()->createUser(
            $this->getFromRequest('username'), 
            $this->getFromRequest('email'),
            $this->getFromRequest('password'),
            $this->_getUserService()->getUserGroupById(
                $this->getFromRequest('group')
            )
        );
        
        return $this->createdResponse(
            $this->generateUrl(
                'view_user', 
                array(
                    'userid' => $user->getId()
                )
            )
        );
    }
    
    /**
     * Remove a user
     * 
     * @param integer $userid User Id
     * 
     * @Route("/user/{userid}", name="delete_user")
     * @Method("DELETE")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUserAction($userid)
    {
        $this->_getUserService()->deleteUser($userid);
        return $this->okResponse();
    }
    
    /**
     * Update a user
     * 
     * @param integer $userid User Id
     * 
     * @Route("/user/{userid}", name="update_user")
     * @Method("PUT")
     * @HMAC(public=false, roles="ADMIN")
     * @Validation\ValidateString(field="username", maxLength=64)
     * @Validation\ValidateEmail(field="email", maxLength=64)
     * @Validation\ValidateString(field="password", maxLength=128)
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateUserAction($userid)
    {
        $this->_getUserService()->updateUser(
            $userid, 
            array(
                'username' => $this->getFromRequest('username'),
                'email' => $this->getFromRequest('email'),
                'password' => $this->getFromRequest('password')
            )
        );
        
        return $this->okResponse();
    }
    
    /**
     * Enable a user
     * 
     * @param integer $userid User Id
     * 
     * @Route("/user/{userid}/enable", name="enable_user")
     * @Method("POST")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enableUserAction($userid)
    {
        $this->_getUserService()->toggleUser($userid, true);
        return $this->okResponse();
    }
    
    /**
     * Disable a user
     * 
     * @param integer $userid User Id
     * 
     * @Route("/user/{userid}/disable", name="disable_user")
     * @Method("POST")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disableUserAction($userid)
    {
        $this->_getUserService()->toggleUser($userid, false);
        return $this->okResponse();
    }
    
    /**
     * Add a role for a given user
     * 
     * @param integer $userid User Id
     * @param integer $roleid Role Id
     * 
     * @Route("/user/{userid}/role/{roleid}", name="add_user_role")
     * @Method("PUT")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addUserRoleAction($userid, $roleid)
    {
        $this->_getUserService()->addUserRole(
            $userid,
            $this->_getRoleService()->getRoleById($roleid)
        );
        return $this->okResponse();
    }
    
    /**
     * Delete a role for a given user
     * 
     * @param integer $userid User Id
     * @param integer $roleid Role Id
     * 
     * @Route("/user/{userid}/role/{roleid}", name="delete_user_role")
     * @Method("DELETE")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUserRoleAction($userid, $roleid)
    {
        $this->_getUserService()->removeUserRole(
            $userid,
            $this->_getRoleService()->getRoleById($roleid)
        );
        return $this->okResponse();
    }
    
    /**
     * List User Groups function
     * 
     * @Route("/usergroup", name="view_groups", defaults={"_format" = "_json", "_filterable" = true})
     * @Method("GET")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listGroupsAction()
    {
        return $this->_getUserService()->getGroups();
    }
    
    /**
     * Create a user group
     * 
     * @Route("/usergroup", name="create_usergroup")
     * @Method("POST")
     * @HMAC(public=false, roles="ADMIN")
     * @Validation\ValidateString(field="name", maxLength=64)
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createUserGroupAction()
    {
        $this->_getUserService()->createUserGroup(
            $this->getFromRequest('name')
        );
        
        return $this->createdResponse(
            $this->generateUrl(
                'view_groups'
            )
        );
    }
    
    /**
     * Remove a user group
     * 
     * @param integer $groupId User Id
     * 
     * @Route("/usergroup/{userid}", name="delete_usergroup")
     * @Method("DELETE")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUserGroupAction($groupId)
    {
        $this->_getUserService()->deleteUserGroup($groupId);
        return $this->okResponse();
    }
    
    /**
     * Return the user service.
     * 
     * @return \AW\HmacBundle\Services\UserService
     */
    private function _getUserService()
    {
        return $this->get('AW_user_service');
    }
    
    // ------------------------------ Role CRUD ----------------------------- //
    
    /**
     * List Roles function
     * 
     * @Route("/role", name="list_role", defaults={"_format" = "_json"})
     * @Method("GET")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listRolesAction()
    {
        return $this->_getRoleService()->getRoles();
    }
    
    /**
     * List Role function
     * 
     * @param integer $roleid Role Id
     * 
     * @Route("/role/{roleid}", name="view_role", defaults={"_format" = "_json"})
     * @Method("GET")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewRoleAction($roleid)
    {
        return $this->_getRoleService()->getRoleById($roleid)->toArray();
    }
    
    /**
     * Add Role function
     * 
     * @Route("/role", name="add_role")
     * @Method("POST")
     * @Validation\ValidateString(field="role", maxLength=64)
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addRoleAction()
    {
        $role = $this->_getRoleService()->createRole(
            $this->getFromRequest('role')
        );
        
        return $this->createdResponse(
            $this->generateUrl(
                'view_role', 
                array(
                    'roleid' => $role->getId()
                )
            )
        );
    }
    
    /**
     * Remove a role
     * 
     * @param integer $roleid Role Id
     * 
     * @Route("/role/{roleid}")
     * @Method("DELETE")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteRoleAction($roleid)
    {
        $this->_getRoleService()->deleteRole($roleid);
        return $this->okResponse();
    }
    
    
    /**
     * Add Role function
     * 
     * @param integer $roleid  Role Id
     * @param integer $routeid Route Id
     * 
     * @Route("/role/{roleid}/route/{routeid}", name="add_role_route")
     * @Method("PUT")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addRoleRouteAction($roleid, $routeid)
    {        
        $this->_getRoleService()->addRoleRoute(
            $roleid,
            $this->_getRouteService()->getRouteById($routeid)
        );
        
        return $this->okResponse();
    }
    
    
    /**
     * Remove Role function
     * 
     * @param integer $roleid  Role Id
     * @param integer $routeid Route Id
     * 
     * @Route("/role/{roleid}/route/{routeid}", name="remove_role_route")
     * @Method("DELETE")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeRoleRouteAction($roleid, $routeid)
    {        
        $this->_getRoleService()->removeRoleRoute(
            $roleid,
            $this->_getRouteService()->getRouteById($routeid)
        );
        
        return $this->okResponse();
    }
    
    
    /**
     * Return the role service.
     * 
     * @return \AW\HmacBundle\Services\RoleService
     */
    private function _getRoleService()
    {
        return $this->get('AW_role_service');
    }
    
    // --------------------------- Role Route CRUD -------------------------- //
    
    /**
     * List Route function
     * 
     * @Route("/route", name="list_route", defaults={"_format" = "_json"})
     * @Method("GET")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listRouteAction()
    {
        return $this->_getRouteService()->getRoutes();
    }
    
    /**
     * List Route function
     * 
     * @param integer $routeid Route Id
     * 
     * @Route("/route/{routeid}", name="view_route", defaults={"_format" = "_json"})
     * @Method("GET")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewRouteAction($routeid)
    {
        return $this->_getRouteService()->getRouteById($routeid)->toArray();
    }
    
    /**
     * Add Role Route function
     * 
     * @Route("/route", name="add_route", defaults={"_format" = "_json"})
     * @Method("POST")
     * @Validation\ValidateString(field="route", maxLength=64)
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addRouteAction()
    {
        $route = $this->_getRouteService()->createRoute(
            $this->getFromRequest('route')
        );
        
        return $this->createdResponse(
            $this->generateUrl(
                'view_route', 
                array(
                    'routeid' => $route->getId()
                )
            )
        );
    }
    
    /**
     * Remove a route
     * 
     * @param integer $routeid Role Id
     * 
     * @Route("/route/{routeid}")
     * @Method("DELETE")
     * @HMAC(public=false, roles="ADMIN")
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteRouteAction($routeid)
    {
        $this->_getRouteService()->deleteRoute($routeid);
        return $this->okResponse();
    }
    
    
    /**
     * Return the route service.
     * 
     * @return \AW\HmacBundle\Services\RouteService
     */
    private function _getRouteService()
    {
        return $this->get('AW_route_service');
    }
}