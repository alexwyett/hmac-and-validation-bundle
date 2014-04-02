<?php

namespace AW\HmacBundle\Tests\Annotations;

use AW\HmacBundle\Tests\TestBase;

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
class ValidationAnnotationTest extends TestBase
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
                    'field' => 'name',
                    'defaultMessage' => 'This is a default message test'
                ),
                array(),
                'This is a default message test',
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
            ),
            array(
                'ValidateDate',
                array(
                    'field' => 'date'
                ),
                array(),
                'date is required',
                -1,
                400
            ),
            array(
                'ValidateDate',
                array(
                    'field' => 'date'
                ),
                array(
                    'date' => 'invaliddateformat'
                ),
                'invaliddateformat is not a valid date',
                -1,
                400
            ),
            array(
                'ValidateDate',
                array(
                    'field' => 'date',
                    'minDate' => 'now'
                ),
                array(
                    'date' => '2012-01-01'
                ),
                '2012-01-01 is less than the minimum date of ' . date('d-m-Y'),
                -1,
                400
            ),
            array(
                'ValidateDate',
                array(
                    'field' => 'date',
                    'minDate' => 'now',
                    'maxDate' => '+1 year'
                ),
                array(
                    'date' => date('d-m-Y', strtotime('+2 years'))
                ),
                date('d-m-Y', strtotime('+2 years')) . ' is greater than the maximum date of ' . date('d-m-Y'),
                -1,
                400
            )
        );
    }
}
