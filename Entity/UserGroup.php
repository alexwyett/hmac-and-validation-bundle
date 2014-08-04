<?php
namespace AW\HmacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * A UserGroup object
 * 
 * @category  Entity
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 *
 * @ORM\Entity
 */
class UserGroup
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
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="group")
     */
    protected $user;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return UserGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
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
     * Add user
     *
     * @param \AW\HmacBundle\Entity\User $user
     * @return UserGroup
     */
    public function addUser(\AW\HmacBundle\Entity\User $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AW\HmacBundle\Entity\User $user
     */
    public function removeUser(\AW\HmacBundle\Entity\User $user)
    {
        $this->user->removeElement($user);
    }
    
    /**
     * Array mapping function
     * 
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName()
        );
    }
}
