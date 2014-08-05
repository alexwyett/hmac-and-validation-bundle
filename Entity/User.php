<?php
namespace AW\HmacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * A User object
 * 
 * @category  Entity
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 *
 * @ORM\Entity
 * @ORM\Table(name="User")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $password;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled = false;

    /** 
     * @ORM\ManyToOne(targetEntity="UserGroup", inversedBy="User")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;
    
    /**
     * Many-To-Many, Unidirectional
     *
     * @var ArrayCollection $groups
     *
     * @ORM\ManyToMany(targetEntity="UserRole")
     * @ORM\JoinTable(name="UserRoles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $role;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->role = new \Doctrine\Common\Collections\ArrayCollection();
        $this->group = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return \AW\HmacBundle\Entity\User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Set the users email
     * 
     * @param string $email Email address
     * 
     * @throws \Exception
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function setEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid Email');
        }
        
        $this->email = $email;
        
        return $this;
    }
    
    /**
     * Return the users email address
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Set the enabled bool
     * 
     * @param boolean $enabled Enabled boolean
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        
        return $this;
    }
    
    /**
     * Return the enabled bool
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Add role
     *
     * @param \AW\HmacBundle\Entity\Role $role
     * @return User
     */
    public function addRole(\AW\HmacBundle\Entity\UserRole $role)
    {
        $this->role[] = $role;

        return $this;
    }

    /**
     * Remove role
     *
     * @param \AW\HmacBundle\Entity\Role $role
     */
    public function removeRole(\AW\HmacBundle\Entity\UserRole $role)
    {
        $this->role->removeElement($role);
    }

    /**
     * Get role
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRole()
    {
        return $this->role;
    }
    
    /**
     * Check if a user has access to a given route
     * 
     * @param string $route Route name
     * 
     * @return boolean
     */
    public function checkAccess($route)
    {
        if ($this->isEnabled()) {
            foreach ($this->getRole() as $role) {
                foreach ($role->getRoutes() as $ro) {
                    if ($ro->getRoute() == $route) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Return an array representation of the User
     * 
     * @return array
     */
    public function toArray()
    {
        $roles = array();
        foreach ($this->getRole() as $role) {
            array_push($roles, $role->toArray());
        }
        
        return array(
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'enabled' => $this->isEnabled(),
            'roles' => $roles,
            'group' => $this->getGroup()->toArray()
        );
    }

    /**
     * Set group
     *
     * @param \AW\HmacBundle\Entity\UserGroup $group
     * @return User
     */
    public function setGroup(\AW\HmacBundle\Entity\UserGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AW\HmacBundle\Entity\UserGroup 
     */
    public function getGroup()
    {
        return $this->group;
    }
}
