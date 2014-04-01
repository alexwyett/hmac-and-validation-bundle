<?php
namespace AW\HmacBundle\Annotations;
use AW\HmacBundle\Exceptions\ValidationCollectionException as VEC;

/**
 * This annotation tells the validation scripts should be grouped and returned
 *
 * @Annotation
 *
 * @category  Annotation
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class ValidationCollection
{
    /**
     * Validation Exceptions
     * 
     * @var array
     */
    protected $exceptions = array();
    
    /**
     * Redirect url
     * 
     * @var string
     */
    protected $redirect;
    
    /**
     * Forward to additional controller
     * 
     * @var string
     */
    protected $forward;
    
    /**
     * Controller
     * 
     * @var \Symfony\Bundle\FrameworkBundle\Controller\Controller
     */
    protected $controller;

    /**
     * Creates a new Validation Collection object
     *
     * @param array $options Annotation options
     * 
     * @return void
     */
    public function __construct(array $options)
    {
        if (isset($options['redirect'])) {
            $this->redirect = $options['redirect'];
        }
        if (isset($options['forward'])) {
            $this->forward = $options['forward'];
        }
    }
    
    /**
     * Return the exceptions array
     * 
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
    
    /**
     * Set the exceptions array
     * 
     * @param array $exceptions
     * 
     * @return \AW\HmacBundle\Annotations\ValidationCollection
     */
    public function setExceptions(array $exceptions)
    {
        $this->exceptions = $exceptions;
        
        return $this;
    }    
    
    /**
     * Set the controller object
     * 
     * @param \Symfony\Bundle\FrameworkBundle\Controller\Controller $controller Controller
     * 
     * @return \AW\HmacBundle\Annotations\ValidationCollection
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        
        return $this;
    }
    
    /**
     * Return the controller
     * 
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Controller
     */
    public function getController()
    {
        return $this->controller;
    }
    
    /**
     * Throw a collective response
     * 
     * @throws AW\HmacBundle\Exceptions\ValidationCollection\Exception
     */
    public function throwException()
    {
        $exception = new VEC('Validation errors have occured', -1, 400);
        $exception->setFields($this->_getFieldsAndMessages());
        
        $this->getController()->get('session')->getFlashBag()->add(
            'errors',
            $this->_getFieldsAndMessages()
        );
        
        if ($this->redirect === true) {
            $exception->setRedirect(
                $this->getController()->getRequest()->headers->get('referer')
            );
        } else if ($this->forward) {
            $exception->setForward(
                $this->getController()->forward($this->forward)
            );
        } else {
            $exception->setRedirect($this->redirect);
        }
        
        throw $exception;
    }
    
    /**
     * Return the fields and exception messages
     * 
     * @return array
     */
    private function _getFieldsAndMessages()
    {
        $fields = array();
        foreach ($this->getExceptions() as $ex) {
            $fields[$ex->getField()] = $ex->getMessage();
        }
        return $fields;
    }
    
    /**
     * Set the request object
     * 
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function _getRequest()
    {
        $this->getController()->get('request');
    }
}