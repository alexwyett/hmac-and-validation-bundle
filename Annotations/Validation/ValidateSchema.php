<?php

namespace AW\HmacBundle\Annotations\Validation;
use AW\HmacBundle\Exceptions\APIException as APIException;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\RefResolver;
use JsonSchema\Validator;

/**
 * This annotation performs some basic parameter validation on POST/PUT requests
 * 
 * @see http://json-schema.org/
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
class ValidateSchema extends Validate
{
    /**
     * Specified and decoded json schema object
     * 
     * @var object
     */
    protected $schema;
    
    /**
     * Creates a new schema validation object
     *
     * @param array $options Annotation options
     * 
     * @return void
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        if (isset($options['schema'])) {
            $retriever = new \JsonSchema\Uri\UriRetriever();
            $this->schema = $retriever->retrieve(
                'file://' . $options['schema']
            );
            $refResolver = new \JsonSchema\RefResolver($retriever);
            $refResolver->resolve($this->schema, 'file://' . __DIR__);
        }
    }
    
    /**
     * Schema validation.
     * 
     * @param array|object $parameters Array of posted parameters.  Parameter
     * could also be an object is json has been posted to the controller.
     * 
     * @throws \AW\HmacBundle\Exceptions\APIException
     * 
     * @return void
     */
    public function validateSchema($parameters)
    {
        // Remove hmacKey/hmacHash
        if (isset($parameters['hmacKey'])) {
            unset($parameters['hmacKey']);
        }
        if (isset($parameters['hmacHash'])) {
            unset($parameters['hmacHash']);
        }
        
        // Encode the json data if a json object has not been supplied
        if (is_array($parameters)) {
            $parameters = json_decode(json_encode($parameters));
        }
        
        // Create new validation object
        $validator = new \JsonSchema\Validator();
        $validator->check($parameters, $this->schema);
        
        // Output errors if not valid
        if (!$validator->isValid()) {
            $errors = '';
            foreach ($validator->getErrors() as $error) {
                $errors .= sprintf(
                    "%s\n",  
                    $error['message']
                );
            }
            $this->setValidationException(
                sprintf(
                    'Validation has failed due to: %s',
                    $errors
                ), 
                -1, 
                400
            );
        }
    }
}