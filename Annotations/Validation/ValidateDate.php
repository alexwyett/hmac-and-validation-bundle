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
class ValidateDate extends Validate
{
    /**
     * Max Date
     * 
     * @var \DateTime
     */
    protected $maxDate;
    
    /**
     * Less than
     * 
     * @var \DateTime
     */
    protected $minDate;
    
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
        
        if (isset($options['maxDate'])) {
            $this->maxDate = new \DateTime(
                $options['maxDate']
            );
        }
        
        if (isset($options['minDate'])) {
            $this->minDate = new \DateTime(
                $options['minDate']
            );
        }
    }
    
    /**
     * Return the maxDate time check
     * 
     * @return DateTime
     */
    public function getMaxDate()
    {
        return $this->maxDate;
    }
    
    /**
     * Return the minDate time check
     * 
     * @return DateTime
     */
    public function getMinDate()
    {
        return $this->minDate;
    }
    
    /**
     * Date Validation function.  Loop through the supplied fields and compare with
     * the supplied parameters.
     * 
     * @throws APIException
     * 
     * @return void
     */
    public function validateDate()
    {
        if (!$this->_isDateValid($this->getValue())) {
            $this->setValidationException(
                sprintf('%s is not a valid date', $this->getValue()),
                -1,
                400
            );
        }
        
        $date = new \DateTime($this->getValue());
        
        // Check date is greater than min date
        if ($date <= $this->getMinDate()) {
            $this->setValidationException(
                sprintf(
                    '%s is less than the minimum date of %s', 
                    $this->getValue(),
                    $this->getMinDate()->format('d-m-Y')
                ),
                -1,
                400
            );
        }
        
        // Check date is less than max date
        if ($date > $this->getMaxDate()) {
            $this->setValidationException(
                sprintf(
                    '%s is greater than the maximum date of %s', 
                    $this->getValue(),
                    $this->getMinDate()->format('d-m-Y')
                ),
                -1,
                400
            );
        }
        
    }
    
    /**
     * Check a string is valid date
     * 
     * @param string $str Date string
     * 
     * @return boolean
     */
    private function _isDateValid($str)
    {
        if (!is_string($str)) {
           return false;
        }

        $stamp = strtotime($str); 
        if (!is_numeric($stamp)) {
           return false; 
        }

        if (!checkdate(date('m', $stamp), date('d', $stamp), date('Y', $stamp))) { 
           return false; 
        } 
        
        return true; 
      } 
}