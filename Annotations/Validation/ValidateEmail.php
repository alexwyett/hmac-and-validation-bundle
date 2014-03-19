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
class ValidateEmail extends ValidateString
{
    /**
     * Email Validation function.  Loop through the supplied fields and compare with
     * the supplied parameters.
     * 
     * @throws \AW\HmacBundle\Exceptions\APIException
     * 
     * @return void
     */
    public function validateEmail()
    {
        if (!filter_var($this->getValue(), FILTER_VALIDATE_EMAIL)) {
            $this->setValidationException(
                sprintf(
                    'Invalid email address: %s',
                    $this->getValue()
                ), 
                -1, 
                400
            );
        }
    }
}