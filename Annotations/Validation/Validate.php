<?php

namespace AW\HmacBundle\Annotations\Validation;
use AW\HmacBundle\Exceptions\ValidationException as ValidationException;

/**
 * This annotation performs some basic parameter validation on POST/PUT requests
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
class Validate
{
    /**
     * Field name to validate
     * 
     * @var string
     */
    private $field;
    
    /**
     * Test value
     * 
     * @var mixed 
     */
    private $value;
    
    /**
     * Field is optional if set to true
     * 
     * @var boolean
     */
    private $optional = false;
    
    /**
     * Symfony kernal
     * 
     * @var \Symfony\Component\HttpKernel
     */
    private $kernel;
    
    /**
     * Default message.  This will override the system error messages if set
     * 
     * @var string
     */
    private $defaultMessage;

    /**
     * Creates a new paramter validation object
     *
     * @param array $options Annotation options
     * 
     * @return void
     */
    public function __construct(array $options)
    {
        if (isset($options['field'])) {
            $this->field = $options['field'];
        }
        if (isset($options['optional'])) {
            $this->optional = $options['optional'];
        }
        if (isset($options['defaultMessage'])) {
            $this->defaultMessage = $options['defaultMessage'];
        }
    }
    
    /**
     * Return the field to validate
     * 
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
    
    /**
     * Return the field value.  This is set by the validateExists function.
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Checkif field is optional or not
     * 
     * @return boolean
     */
    public function isOptional()
    {
        return $this->optional;
    }
    
    /**
     * Set the symfony kernel
     * 
     * @param \Symfony\Component\HttpKernel $kernel HttpKernel
     * 
     * @return \AW\HmacBundle\Annotations\Validation\Validate
     */
    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
        
        return $this;
    }
    
    /**
     * Get the symfony kernal
     * 
     * @return \Symfony\Component\HttpKernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }
    
    /**
     * Return the default message
     * 
     * @return string
     */
    public function getDefaultMessage()
    {
        return $this->defaultMessage;
    }

    /**
     * Validation function.  Loop through the supplied fields and compare with
     * the supplied parameters.
     * 
     * @param array $parameters POST/PUT parameters
     * 
     * @throws APIException
     * 
     * @return void
     */
    public function validate($parameters)
    {
        $methods = array_reverse(
            array_filter(
                get_class_methods($this),
                function($method) {
                    return (
                        (substr($method, 0, 8) ==  'validate')
                        && ($method != 'validate')
                    );
                }
            )
        );
            
        // Set the value to be used
        if ($this->getField() 
            && is_array($parameters)
            && array_key_exists($this->getField(), $parameters)
        ) {
            $this->value = $parameters[$this->getField()];
        }
        
        foreach ($methods as $method) {
            $refMethod = new \ReflectionMethod($this, $method);
            
            // If field is optional and is not supplied, skip it
            if ($this->isOptional() && is_null($this->getValue())) {
                continue;
            }
            
            // Otherwise, validate.  Methods will throw an exception on failure
            if (count($refMethod->getParameters()) == 1) {
                $this->$method($parameters);
            } else {
                $this->$method();
            }
        }
    }

    /**
     * Validation function.  Loop through the supplied fields and compare with
     * the supplied parameters.
     * 
     * @param array $parameters POST/PUT parameters
     * 
     * @throws APIException
     * 
     * @return void
     */
    public function validateExists($parameters)
    {
        if (is_array($parameters)
            && !array_key_exists($this->getField(), $parameters) 
            && !is_null($this->getField())
        ) {
            $this->setValidationException(
                $this->getField() . ' is required', 
                -1, 
                400
            );
        }
    }
    
    /**
     * Throw an apiexception.  This function has been added incase any logging
     * is required. Easier to add it into one place.
     * 
     * @param string  $message Exception message
     * @param integer $code    Exception code
     * @param integer $status  HTTP Status code
     * 
     * @throws \AW\HmacBundle\Exceptions\APIException
     * 
     * @return void
     */
    protected function setValidationException($message, $code, $status)
    {
        if ($this->getDefaultMessage()) {
            $message = $this->getDefaultMessage();
        }
        
        throw new ValidationException(
            $this->getField(), 
            $message, 
            $code, 
            $status
        );
    }
}