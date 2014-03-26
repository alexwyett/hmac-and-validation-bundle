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
     * Throw a collective response
     * 
     * @throws AW\HmacBundle\Exceptions\ValidationCollection\Exception
     */
    public function throwException()
    {
        $exception = new VEC('Validation errors have occured', -1, 400);
        $exception->setFields($this->_getFieldsAndMessages());
        $exception->setRedirect($this->redirect);
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
}