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
 * @ORM\Table(name="ApiUser",indexes={@index(name="apikey", columns={"apikey"})})
 */
class ApiUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=64)
     */
    protected $apikey;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $apisecret;

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $email;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled = false;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles = array();

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
     * Set apikey
     *
     * @param string $apikey
     *
     * @return \AW\HmacBundle\Entity\User
     */
    public function setApikey($apikey)
    {
        $this->apikey = $apikey;

        return $this;
    }

    /**
     * Get apikey
     *
     * @return string
     */
    public function getApikey()
    {
        return $this->apikey;
    }

    /**
     * Set apisecret
     *
     * @param string $apisecret
     *
     * @return \AW\HmacBundle\Entity\User
     */
    public function setApisecret($apisecret)
    {
        $this->apisecret = $apisecret;

        return $this;
    }

    /**
     * Get apisecret
     *
     * @return string
     */
    public function getApisecret()
    {
        return $this->apisecret;
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
     * Add a role
     * 
     * @param string $role Role
     * 
     * @return \AW\HmacBundle\Entity\User
     */
    public function addRole($role)
    {
        if (!$this->isRole($role)) {
            $this->roles[] = strtoupper($role);
        }

        return $this;
    }
    
    /**
     * Return the roles for the user
     * 
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
    
    /**
     * Set the user roles
     * 
     * @param array $roles User roles
     * 
     * @return \AW\HmacBundle\Entity\ApiUser
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        
        return $this;
    }
    
    /**
     * Check if a user is a certain role
     * 
     * @param string $role Role
     * 
     * @return boolean
     */
    public function isRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles());
    }
    
    /**
     * Secret accessor
     * 
     * @param string $secret Secret
     * 
     * @return \AW\HmacBundle\Entity\ApiUser
     */
    public function setSecret($secret)
    {
        return $this->setApisecret($secret);
    } 
           
    
    /**
     * Return an array representation of the ApiUser
     * 
     * @return array
     */
    public function toArray()
    {
        return array(
            'key' => $this->getApikey(),
            'secret' => $this->getApisecret(),
            'email' => $this->getEmail(),
            'roles' => $this->getRoles(),
            'enabled' => $this->isEnabled()
        );
    }
}