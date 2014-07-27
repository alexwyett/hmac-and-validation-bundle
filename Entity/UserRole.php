<?php
namespace AW\HmacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * A User Role object
 * 
 * @category  Entity
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 *
 * @ORM\Entity
 * @ORM\Table(name="UserRole")
 */
class UserRole
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
    protected $role;
    
    /**
     * Many-To-Many, Unidirectional
     *
     * @var ArrayCollection $permissions
     *
     * @ORM\ManyToMany(targetEntity="RoleRoute")
     * @ORM\JoinTable(name="UserRoleRoute",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="route_id", referencedColumnName="id")}
     * )
     */
    protected $routes;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Return the role id
     * 
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return UserRole
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Add routes
     *
     * @param \AW\HmacBundle\Entity\RoleRoute $routes
     * @return UserRole
     */
    public function addRoute(\AW\HmacBundle\Entity\RoleRoute $routes)
    {
        $this->routes[] = $routes;

        return $this;
    }

    /**
     * Remove routes
     *
     * @param \AW\HmacBundle\Entity\RoleRoute $routes
     */
    public function removeRoute(\AW\HmacBundle\Entity\RoleRoute $routes)
    {
        $this->routes->removeElement($routes);
    }

    /**
     * Get routes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    
    /**
     * Array mapping function
     * 
     * @return array
     */
    public function toArray()
    {
        $routes = array();
        foreach ($this->getRoutes() as $route) {
            array_push($routes, $route->toArray());
        }
        return array(
            'id' => $this->getId(),
            'role' => $this->getRole(),
            'routes' => $routes
        );
    }
}
