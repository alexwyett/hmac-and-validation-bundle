<?php

namespace AW\HmacBundle\Services;

use AW\HmacBundle\Exceptions\APIException;
use AW\HmacBundle\Entity\User;
use AW\HmacBundle\Entity\UserRole;
use AW\HmacBundle\Entity\RoleRoute;

/**
 * Handles User crud
 *
 * @category  Services
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class UserService
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
     * Return the list of Users
     * 
     * @return array
     */
    public function getUsers()
    {
        $users = array();
        $usersEm = $this->em->getRepository('AWHmacBundle:User')->findAll();
        foreach ($usersEm as $user) {
            $users[] = $user->toArray();
        }
        
        return $users;
    }
    
    /**
     * Return the list of UserGroups
     * 
     * @return array
     */
    public function getGroups()
    {
        $groups= array();
        $groupsEm = $this->em->getRepository('AWHmacBundle:UserGroup')->findAll();
        foreach ($groupsEm as $group) {
            $groups[] = $group->toArray();
        }
        
        return $groups;
    }
    
    /**
     * Check if a user has access to a given route
     * 
     * @param integer $userid User Id
     * @param string  $route  Route name
     * 
     * @return boolean
     */
    public function checkRole($userid, $route)
    {
        $user = $this->getUserById($userid);
        
        return $user->checkAccess($route);
    }
    
    /**
     * Add a role to a user
     * 
     * @param string                         $userid Role ID
     * @param \AW\HmacBundle\Entity\UserRole $role   Role
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function addUserRole($userid, $role)
    {
        $user = $this->getUserById($userid);
        
        // Check for role existence
        if ($user->getRole()->contains($role)) {
            throw new APIException(
                sprintf(
                    'Role \'%s\' already exists for user \'%s\'',
                    $role->getRole(),
                    $user->getId()
                ),
                -1,
                400
            );
        }
        
        $user->addRole($role);
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }
    
    /**
     * Remove a role from a user
     * 
     * @param string                         $userid Role ID
     * @param \AW\HmacBundle\Entity\UserRole $role   Role
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function removeUserRole($userid, $role)
    {
        $user = $this->getUserById($userid);
        
        // Check for role existence
        if (!$user->getRole()->contains($role)) {
            throw new APIException(
                sprintf(
                    'User \'%s\' is not a member of \'%s\' role',
                    $user->getId(),
                    $role->getRole()
                ),
                -1,
                400
            );
        }
        
        $user->removeRole($role);
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }
    
    /**
     * Add a group to a user
     * 
     * @param string                          $userid Role ID
     * @param \AW\HmacBundle\Entity\UserGroup $group  Group
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function addUserGroup($userid, $group)
    {
        $user = $this->getUserById($userid);
        
        // Check for role existence
        if ($user->getGroup()->contains($group)) {
            throw new APIException(
                sprintf(
                    'Group \'%s\' already exists for user \'%s\'',
                    $group->getName(),
                    $user->getId()
                ),
                -1,
                400
            );
        }
        
        $user->addGroup($group);
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }
    
    /**
     * Remove a group from a user
     * 
     * @param string                          $userid Role ID
     * @param \AW\HmacBundle\Entity\UserGroup $group  Group
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function removeUserGroup($userid, $group)
    {
        $user = $this->getUserById($userid);
        
        // Check for role existence
        if (!$user->getRole()->contains($group)) {
            throw new APIException(
                sprintf(
                    'User \'%s\' is not a member of \'%s\' role',
                    $user->getId(),
                    $group->getName()
                ),
                -1,
                400
            );
        }
        
        $user->removeGroup($group);
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }
    
    /**
     * User creation
     * 
     * @param string                          $username User Name
     * @param string                          $email    User Email
     * @param string                          $password User Password
     * @param \AW\HmacBundle\Entity\UserGroup $group    User Group
     * 
     * @throws APIException
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function createUser($username, $email, $password, $group)
    {
        if ($this->_checkUserExists($username, $email, $password, $group)) {
            throw new APIException('User already exists', -1, 400);
        }
        
        $user = new \AW\HmacBundle\Entity\User();
        $user->setUsername($username)
            ->setEmail($email)
            ->setPassword($password)
            ->setGroup($group);
        
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
    
    /**
     * Update a given user with a given key value parameter set
     * 
     * @param string $userid User Id
     * @param array  $params Key/Val pair of params. Key will be converted into
     * and accessor name to set on found user object.
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function updateUser($userid, array $params)
    {
        $user = $this->getUserById($userid);
        foreach ($params as $key => $val) {
            $func = 'set' . ucfirst($key);
            if (method_exists($user, $func)) {
                $user->$func($val);
            }
        }
        
        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }
    
    /**
     * Enable/Disable the api user
     * 
     * @param string  $userid  User Id
     * @param boolean $enabled Enabled
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function toggleUser($userid, $enabled = false)
    {
        $user = $this->getUserById($userid);
        $user->setEnabled($enabled);
        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }
    
    /**
     * Remove a user
     * 
     * @param string $userid User ID
     * 
     * @return boolean
     */
    public function deleteUser($userid)
    {
        $user = $this->getUserById($userid);
        $this->em->remove($user);
        $this->em->flush();
        
        return true;
    }
    
    /**
     * Get user object
     * 
     * @param string $userid User Id
     * 
     * @throws APIException
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function getUserById($userid)
    {
        $user = $this->em->getRepository(
            'AWHmacBundle:User'
        )->findOneById($userid);
        
        if ($user) {
            return $user;
        } else {
            throw new APIException('User not found: ' . $userid, -1, 404);
        }
    }
    
    /**
     * Create a usergroup object
     * 
     * @param string $name Group Name
     * 
     * @throws APIException
     * 
     * @return \AW\HmacBundle\Entity\UserGroup
     */
    public function createUserGroup($name)
    {
        if ($this->_checkUserGroupExists($name)) {
            throw new APIException('User Group already exists', -1, 400);
        }
        
        $group = new \AW\HmacBundle\Entity\UserGroup();
        $group->setName($name);
        
        $this->em->persist($group);
        $this->em->flush();

        return $group;
    }
    
    /**
     * Remove a user group
     * 
     * @param string $groupId User Group id
     * 
     * @return boolean
     */
    public function deleteUserGroup($groupId)
    {
        $group = $this->getUserGroupById($groupId);
        $this->em->remove($group);
        $this->em->flush();
        
        return true;
    }
    
    /**
     * Get user group object by id
     * 
     * @param string $groupId User Group id
     * 
     * @throws APIException
     * 
     * @return \AW\HmacBundle\Entity\UserGroup
     */
    public function getUserGroupById($groupId)
    {
        $group = $this->em->getRepository(
            'AWHmacBundle:UserGroup'
        )->findOneById($groupId);
        
        if ($group) {
            return $group;
        } else {
            throw new APIException('User Group not found: ' . $groupId, -1, 404);
        }
    }
    
    /**
     * Login function
     * 
     * @param string $username User Name
     * @param string $password User Password
     * 
     * @return boolean
     */
    public function getUserByLogin($username, $password)
    {
        try {
            return $this->_getUserByUsernameAndPassword($username, $password);
        } catch (APIException $ex) {
            return false;
        }
    }
    
    /**
     * Get user object
     * 
     * @param string $username User Name
     * @param string $password User Password
     * 
     * @throws APIException
     * 
     * @return array
     */
    private function _getUserByUsernameAndPassword($username, $password)
    {
        return $this->_getUserByParams(
            array(
                'username' => $username,
                'password' => $password
            )
        );
    }
    
    /**
     * Get user object by an array of params
     * 
     * @param array $params Key/Val array
     * 
     * @throws APIException
     * 
     * @return Array
     */
    private function _getUserByParams($params)
    {
        $user = $this->em->getRepository(
            'AWHmacBundle:User'
        )->findBy(
            $params
        );
        
        if ($user) {
            return $user;
        } else {
            throw new APIException(
                sprintf(
                    'User not found: %s',
                    implode($params)
                ),
                -1,
                404
            );
        }
    }
    
    /**
     * Check if a user exists or not
     * 
     * @param string                          $username User Name
     * @param string                          $email    User Email
     * @param string                          $password User Password
     * @param \AW\HmacBundle\Entity\UserGroup $group    User Group
     * 
     * @return boolean
     */
    private function _checkUserExists($username, $email, $password, $group)
    {
        try {
            $users = $this->_getUserByParams(
                array(
                    'username' => $username,
                    'email' => $email,
                    'password' => $password
                )
            );
        } catch (\RuntimeException $ex) {
            return false;
        }
        
        if ($users) {
            foreach ($users as $user) {
                if ($user->getGroup() === $group) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if a group exists or not
     * 
     * @param string $groupname Name
     * 
     * @return boolean
     */
    private function _checkUserGroupExists($groupname)
    {
        try {
            $groups = $this->getGroups();
            foreach ($groups as $group) {
                if ($group['name'] === $groupname) {
                    return true;
                }
            }
            
            return false;
        } catch (APIException $ex) {
            return false;
        }
    }
}
