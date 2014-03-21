<?php

namespace AW\HmacBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AW\HmacBundle\Exceptions\APIException;

/**
 * Base controller
 *
 * @category  Controller
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
abstract class DefaultController extends Controller
{
    /**
     * Return a json encoded response
     *
     * @param array   $data   Array to json encode
     * @param integer $status HTTP Status code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jsonResponse($data, $status = 200)
    {
        return new \Symfony\Component\HttpFoundation\Response(
            json_encode($data),
            $status
        );
    }

    /**
     * Return a created response
     *
     * @param string  $location Redirected location
     * @param integer $status   HTTP Status code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createdResponse($location, $status = 201)
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode($status);
        $response->headers->set('Content-Location', $location);

        return $response;
    }

    /**
     * Return a created response
     *
     * @param integer $status HTTP Status code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function okResponse($status = 204)
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode($status);

        return $response;
    }


    /**
     * If the key is null in the query then the request is checked
     *
     * @param string $key The key to find
     * @param string $def The default value.
     *
     * @return String value of the key or null if not found
     */
    public function getFromRequest($key, $def = null)
    {
        $val = $this->getRequest()->query->get($key, $def);
        if (!$val) {
            $val = $this->getRequest()->request->get($key, $def);
        }

        return $val;
    }


    /**
     * Gets the JSON data from the request
     *
     * @param boolean $convertToObject Convert to an object instead of an array
     *
     * @return array
     */
    public function getPostData($convertToObject = false)
    {
        $val = $this->getRequest()->query->all();
        if (!$val) {
            $val = $this->getRequest()->request->all();
        }

        if ($convertToObject && is_array($val)) {
            return json_decode(json_encode($val));
        } else {
            return $val;
        }
    }

}