<?php

namespace AW\HmacBundle\Tests\Annotations;

use AW\HmacBundle\Tests\ToccTest;

/**
 * Test the valiation annotation clases
 *
 * @category  Tests
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class ValidationAnnotationTest extends ToccTest
{
    /**
     * Validation test function
     * 
     * @dataProvider validationProvider
     * 
     * @return void
     */
    public function testValidate(
        $class, 
        $options, 
        $params, 
        $exceptionString, 
        $exceptionCode, 
        $exceptionStatus
    ) {
        $validationClass = sprintf(
            '\AW\HmacBundle\Annotations\Validation\%s',
            $class
        );
        $validation = new $validationClass($options);
        
        try {
            $validation->validate($params);
        } catch (\AW\HmacBundle\Exceptions\APIException $ex) {
            $this->assertEquals($ex->getMessage(), $exceptionString);
            $this->assertEquals($ex->getCode(), $exceptionCode);
            $this->assertEquals($ex->getHTTPStatusCode(), $exceptionStatus);
        }
    }
    
    /**
     * Return tests for validation
     * 
     * @return array
     */
    public function validationProvider()
    {
        return array(
            array(
                'Validate',
                array(
                    'field' => 'name'
                ),
                array(),
                'name is required',
                -1,
                400
            ),
            array(
                'Validate',
                array(
                    'field' => 'name'
                ),
                array(
                    'name' => null
                ),
                'name is required',
                -1,
                400
            ),
            array(
                'ValidateString',
                array(
                    'field' => 'name'
                ),
                array(
                    'name' => null
                ),
                'name is not a string',
                -1,
                400
            ),
            array(
                'ValidateString',
                array(
                    'field' => 'name',
                    'maxLength' => 5
                ),
                array(
                    'name' => 'string greater than 5'
                ),
                'name should be less than or equal to 5',
                -1,
                400
            ),
            array(
                'ValidateString',
                array(
                    'field' => 'name',
                    'maxLength' => 10,
                    'minLength' => 5
                ),
                array(
                    'name' => 'foo'
                ),
                'name should be greater than or equal to 5',
                -1,
                400
            ),
            array(
                'ValidateString',
                array(
                    'field' => 'name',
                    'maxLength' => 10,
                    'minLength' => 5
                ),
                array(
                    'name' => 'foo bar foo bar'
                ),
                'name should be less than or equal to 10',
                -1,
                400
            ),
            array(
                'ValidateNumber',
                array(
                    'field' => 'age'
                ),
                array(
                    'age' => 'foo'
                ),
                'age is not a number',
                -1,
                400
            ),
            array(
                'ValidateEmail',
                array(
                    'field' => 'email'
                ),
                array(),
                'email is required',
                -1,
                400
            ),
            array(
                'ValidateEmail',
                array(
                    'field' => 'email',
                    'maxLength' => 20
                ),
                array(
                    'email' => 'foobarinvalidemailbarfoo'
                ),
                'email should be less than or equal to 20',
                -1,
                400
            ),
            array(
                'ValidateRegex',
                array(
                    'field' => 'foo',
                    'pattern' => '/[a-z]+/'
                ),
                array(
                    'foo' => ''
                ),
                '`` in foo fails regex pattern /[a-z]+/',
                -1,
                400
            ),
            array(
                'ValidateRegex',
                array(
                    'field' => 'foo',
                    'pattern' => '/[a-z]+/'
                ),
                array(
                    'foo' => '*(&(ASD)*(S)*)AS'
                ),
                '`*(&(ASD)*(S)*)AS` in foo fails regex pattern /[a-z]+/',
                -1,
                400
            ),
            array(
                'ValidateRegex',
                array(
                    'field' => 'foo',
                    'pattern' => '/[1-9]+/'
                ),
                array(
                    'foo' => 'jjhgjgj'
                ),
                '`jjhgjgj` in foo fails regex pattern /[1-9]+/',
                -1,
                400
            )
        );
    }
}