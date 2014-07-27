<?php

namespace AW\HmacBundle\Services;

use AW\HmacBundle\Exceptions\APIException;
use AW\HmacBundle\Entity\User;
use AW\HmacBundle\Entity\UserRole;
use AW\HmacBundle\Entity\RoleRoute;

/**
 * Handles  crud
 *
 * @category  Services
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class RouteService
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
     * Return the list of Routes
     * 
     * @return array
     */
    public function getRoutes()
    {
        $routes = array();
        $routesEm = $this->em->getRepository('AWHmacBundle:RoleRoute')->findAll();
        foreach ($routesEm as $route) {
            $routes[] = $route->toArray();
        }
        
        return $routes;
    }
    
    /**
     * Get route object
     * 
     * @param string $routeid Route Id
     * 
     * @throws APIException
     * 
     * @return \AW\HmacBundle\Entity\RoleRoute
     */
    public function getRouteById($routeid)
    {
        $route = $this->em->getRepository(
            'AWHmacBundle:RoleRoute'
        )->findOneById($routeid);
        
        if ($route) {
            return $route;
        } else {
            throw new APIException('Route not found: ' . $routeid, -1, 404);
        }
    }
    
    /**
     * Route creation
     * 
     * @param string $routeName Route definition
     * 
     * @throws APIException
     * 
     * @return \AW\HmacBundle\Entity\RoleRoute
     */
    public function createRoute($routeName)
    {
        if ($this->_checkRouteExists($routeName)) {
            throw new APIException('Route already exists', -1, 400);
        }
        
        $route = new \AW\HmacBundle\Entity\RoleRoute();
        $route->setRoute($routeName);
        
        $this->em->persist($route);
        $this->em->flush();

        return $route;
    }
    
    /**
     * Remove a route
     * 
     * @param string $routeId Route ID
     * 
     * @return void
     */
    public function deleteRoute($routeId)
    {
        $route = $this->getRouteById($routeId);        
        $this->em->remove($route);
        $this->em->flush();
    }
    
    /**
     * Check if a route exists or not
     * 
     * @param string $route Route description
     * 
     * @return boolean
     */
    private function _checkRouteExists($route)
    {
        $route = $this->em->getRepository(
            'AWHmacBundle:RoleRoute'
        )->findOneByRoute($route);
        
        if ($route) {
            return true;
        } else {
            return false;
        }
    }
}