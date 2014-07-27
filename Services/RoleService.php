<?php

namespace AW\HmacBundle\Services;

use AW\HmacBundle\Exceptions\APIException;
use AW\HmacBundle\Entity\User;
use AW\HmacBundle\Entity\UserRole;
use AW\HmacBundle\Entity\RoleRoute;

/**
 * Handles Role crud
 *
 * @category  Services
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class RoleService
{
    /**
     * Entity Manager
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Constructor
     *
     * @param \Doctrine\ORM\EntityManager $em The entity manager
     * 
     * @return void
     */
    public function __construct($em)
    {
        $this->em = $em;
    }
    
    /**
     * Return the list of roles
     * 
     * @return array
     */
    public function getRoles()
    {
        $roles = array();
        $rolesEm = $this->em->getRepository('AWHmacBundle:UserRole')->findAll();
        foreach ($rolesEm as $role) {
            $roles[] = $role->toArray();
        }
        
        return $roles;
    }
    
    /**
     * Get role object
     * 
     * @param string $roleid Role Id
     * 
     * @throws APIException
     * 
     * @return \AW\HmacBundle\Entity\UserRole
     */
    public function getRoleById($roleid)
    {
        $role = $this->em->getRepository(
            'AWHmacBundle:UserRole'
        )->findOneById($roleid);
        
        if ($role) {
            return $role;
        } else {
            throw new APIException('Role not found: ' . $roleid, -1, 404);
        }
    }
    
    /**
     * Role creation
     * 
     * @param string $roleName Role definition
     * 
     * @throws APIException
     * 
     * @return \AW\HmacBundle\Entity\UserRole
     */
    public function createRole($roleName)
    {
        if ($this->_checkRoleExists($roleName)) {
            throw new APIException('Role already exists', -1, 400);
        }
        
        $role = new \AW\HmacBundle\Entity\UserRole();
        $role->setRole($roleName);
        
        $this->em->persist($role);
        $this->em->flush();

        return $role;
    }
    
    /**
     * Update a given role with a given key value parameter set
     * 
     * @param string $roleId Role ID
     * @param array  $params Key/Val pair of params. Key will be converted into
     * and accessor name to set on found user object.
     * 
     * @return \AW\HmacBundle\Entity\UserRole
     */
    public function updateRole($roleId, array $params)
    {
        $role = $this->getRoleById($roleId);
        foreach ($params as $key => $val) {
            $func = 'set' . ucfirst($key);
            if (method_exists($role, $func)) {
                $role->$func($val);
            }
        }
        
        $this->em->persist($role);
        $this->em->flush();
        
        return $role;
    }
    
    /**
     * Add a role route
     * 
     * @param string                          $roleId Role ID
     * @param \AW\HmacBundle\Entity\RoleRoute $route  Role Route Object
     * 
     * @return \AW\HmacBundle\Entity\UserRole
     */
    public function addRoleRoute($roleId, $route)
    {
        $role = $this->getRoleById($roleId);
        
        // Check for route existence
        if ($role->getRoutes()->contains($route)) {
            throw new APIException(
                sprintf(
                    'Route \'%s\' already exists for role \'%s\'',
                    $route->getRoute(),
                    $role->getRole()
                ),
                -1,
                400
            );
        }
        
        $role->addRoute($route);
        
        $this->em->persist($role);
        $this->em->flush();
        
        return $role;
    }
    
    /**
     * Remove a role route
     * 
     * @param string                          $roleId Role ID
     * @param \AW\HmacBundle\Entity\RoleRoute $route  Role Route Object
     * 
     * @return \AW\HmacBundle\Entity\UserRole
     */
    public function removeRoleRoute($roleId, $route)
    {
        $role = $this->getRoleById($roleId);
        
        // Check for route existence
        if (!$role->getRoutes()->contains($route)) {
            throw new APIException(
                sprintf(
                    'Route \'%s\' is not assigned to role \'%s\'',
                    $route->getRoute(),
                    $role->getRole()
                ),
                -1,
                400
            );
        }
        
        $role->removeRoute($route);
        
        $this->em->persist($role);
        $this->em->flush();
        
        return $role;
    }
    
    /**
     * Remove a role
     * 
     * @param string $roleId Role ID
     * 
     * @return void
     */
    public function deleteRole($roleId)
    {
        $role = $this->getRoleById($roleId);
        $query = $this->em->getConnection()->query(
            sprintf(
                'select * from UserRoleRoute where role_id=%s',
                $role->getId()
            )
        );
        $routes = $query->fetchAll();
        if (count($routes) > 0) {
            throw new APIException('Role has routes assinged', -1, 500);
        }
        
        $this->em->remove($role);
        $this->em->flush();
    }
    
    /**
     * Check if a role exists or not
     * 
     * @param string $role Role description
     * 
     * @return boolean
     */
    private function _checkRoleExists($role)
    {
        $role = $this->em->getRepository(
            'AWHmacBundle:UserRole'
        )->findOneByRole($role);
        
        if ($role) {
            return true;
        } else {
            return false;
        }
    }
}
