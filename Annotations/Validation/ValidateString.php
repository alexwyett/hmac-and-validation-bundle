<?php

namespace AW\HmacBundle\Annotations\Validation;
use AW\HmacBundle\Exceptions\APIException as APIException;

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
class ValidateString extends Validate
{
    /**
     * Do we want to check for max length?
     * 
     * @var boolean
     */
    private $maxLength = false;
    
    /**
     * Do we want to check for min length?
     * 
     * @var boolean
     */
    private $minLength = false;

    /**
     * Creates a new paramter validation object
     *
     * @param array $options Annotation options
     * 
     * @return void
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        
        if (isset($options['maxLength'])) {
            $this->maxLength = $options['maxLength'];
        }
        
        if (isset($options['minLength'])) {
            $this->minLength = $options['minLength'];
        }
    }
    
    /**
     * Check if the max length check is set or not
     * 
     * @return boolean
     */
    public function isMaxLengthCheck()
    {
        return !is_bool($this->maxLength) && is_integer($this->maxLength);
    }
    
    /**
     * Check if the min length check is set or not
     * 
     * @return boolean
     */
    public function isMinLengthCheck()
    {
        return !is_bool($this->minLength) && is_integer($this->minLength);
    }
    
    /**
     * Return the maxLength check
     * 
     * @return mixed
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }
    
    /**
     * Return the minLength check
     * 
     * @return mixed
     */
    public function getMinLength()
    {
        return $this->minLength;
    }
    
    /**
     * String Validation function.  Loop through the supplied fields and compare with
     * the supplied parameters.
     * 
     * @throws APIException
     * 
     * @return void
     */
    public function validateString()
    {
        if (!is_string($this->getValue())) {
            $this->setValidationException(
                sprintf(
                    '%s is not a string',
                    $this->getField()
                ), 
                -1, 
                400
            );
        }
        
        if ($this->isMaxLengthCheck()) {
            if (strlen($this->getValue()) >= $this->getMaxLength()) {
                $this->setValidationException(
                    sprintf(
                        '%s should be less than or equal to %s',
                        $this->getField(),
                        $this->getMaxLength()
                    ), 
                    -1, 
                    400
                );
            }
        }
        
        if ($this->isMinLengthCheck()) {
            if (strlen($this->getValue()) <= $this->getMinLength()) {
                $this->setValidationException(
                    sprintf(
                        '%s should be greater than or equal to %s',
                        $this->getField(),
                        $this->getMinLength()
                    ), 
                    -1, 
                    400
                );
            }
        }
    }
}