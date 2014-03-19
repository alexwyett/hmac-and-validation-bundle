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
class ValidateNumber extends Validate
{
    /**
     * Numeric Validation function
     * 
     * @throws \AW\HmacBundle\Exceptions\APIException
     * 
     * @return void
     */
    public function validateNumer()
    {
        if (!is_numeric($this->getValue())) {
            $this->setValidationException(
                sprintf(
                    '%s is not a number',
                    $this->getField()
                ), 
                -1, 
                400
            );
        }
    }
}