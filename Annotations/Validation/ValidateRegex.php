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
class ValidateRegex extends Validate
{
    /**
     * Regex pattern
     * 
     * @var string
     */
    private $pattern;

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
        
        if (isset($options['pattern'])) {
            $this->pattern = $options['pattern'];
        } else {
            throw new APIException('Regex pattern must be supplied', -1, 500);
        }
    }
    
    /**
     * Return the regex pattern
     * 
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }
    
    /**
     * Regex pattern validation function.  
     * 
     * @throws APIException
     * 
     * @return void
     */
    public function validateRegex()
    {   
        if ($this->getPattern() 
            && !preg_match($this->getPattern(), $this->getValue())
        ) {
            throw new APIException(
                sprintf(
                    '`%s` in %s fails regex pattern %s',
                    $this->getValue(),
                    $this->getField(),
                    $this->getPattern()
                ),
                -1,
                400
            );
        }
    }
}