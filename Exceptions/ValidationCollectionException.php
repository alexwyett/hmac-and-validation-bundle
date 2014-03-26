<?php

namespace AW\HmacBundle\Exceptions;

use \Symfony\Component\HttpFoundation\Response;

/**
 * Exception class for the HmacBundle.
 *
 * @category  Exceptions
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class ValidationCollectionException extends APIException
{
    /**
     * Fields which threw an exception
     * 
     * @var string
     */
    protected $fields = array();
    
    /**
     * Redirect response
     * 
     * @var string
     */
    private $redirect;
    
    /**
     * Forward response
     * 
     * @var \Symfony\Component\HttpFoundation\Response
     */
    private $forward;
    
    /**
     * Set the errored fields
     * 
     * @param array $fields Fields which threw an exception
     * 
     * @return \AW\HmacBundle\Exceptions\ValidationCollectionException
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        
        return $this;
    }
    
    /**
     * Set the errored fields
     * 
     * @param array  $field   Field which threw an exception
     * @param string $message Exception message
     * 
     * @return \AW\HmacBundle\Exceptions\ValidationCollectionException
     */
    public function setField($field, $message)
    {
        $this->fields[$field] = $message;
        
        return $this;
    }
    
    /**
     * Set the redirect url
     * 
     * @param string $redirect Redirect url
     * 
     * @return \AW\HmacBundle\Exceptions\APIException
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
        
        return $this;
    }
    
    /**
     * Set the forward url
     * 
     * @param string $forward Forward url
     * 
     * @return \AW\HmacBundle\Exceptions\APIException
     */
    public function setForward($forward)
    {
        $this->forward = $forward;
        
        return $this;
    }
    
    /**
     * Return the content for the exception
     * 
     * @return array
     */
    public function getErrorContent()
    {
        $content = parent::getErrorContent();
        return array_merge(
            array(
                'errorFields' => $this->getFields()
            ), 
            $content
        );
    }
    
    /**
     * Return the errored fields
     * 
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }    
    
    /**
     * Return a default json response
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        if($this->forward) {
            $this->forward->headers->set(
                'Validation-Errors', 
                json_encode($this->getFields())
            );
            return $this->forward;
        }
        
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode($this->getHTTPStatusCode());
        if ($this->redirect) {
            $response->setStatusCode(302);
            $response->headers->set('Location', $this->redirect);
            $response->headers->set(
                'Validation-Errors', 
                json_encode($this->getFields())
            );
        } else {
            $response->setContent(json_encode($this->getErrorContent()));
            $response->headers->set('Content-Type', 'application/json');
        }
        return $response;
    }
}
