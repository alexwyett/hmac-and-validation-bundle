<?php

namespace AW\HmacBundle\Tests\Controller;

use AW\HmacBundle\Tests\TestBase;

class UserControllerTest extends TestBase
{
    /**
     * Test the add new user endpoint
     * 
     * @return void
     */
    public function testCreateUser()
    {
        extract(
            $this->doRequest(
                '/hmac/user',
                'POST',
                array(
                    'username' => 'alex',
                    'email' => 'alex@carltonsoftware.co.uk',
                    'password' => 'password'
                )
            )
        );
        
        $this->assertEquals(201, $status);
    }
    
    /**
     * Test invalid requests to create api endpoint
     * 
     * @param array   $params         Post Params
     * @param integer $expectedStatus Expected Exception status code
     * 
     * @dataProvider getInvalidApiUserData
     * 
     * @return void
     */
    public function testCreateApiUserException($params, $expectedStatus)
    {
        extract(
            $this->doRequest(
                '/hmac/user',
                'POST',
                $params
            )
        );
        
        $this->assertEquals($status, $expectedStatus);
    }
    
    /**
     * testCreateApiUserException data provider
     * 
     * @return array
     */
    public function getInvalidApiUserData()
    {
        return array(
            array(
                'params' => array(),
                400
            ),
            array(
                'params' => array(
                    'key' => null,
                    'email' => null,
                    'password' => null
                ),
                400
            ),
            array(
                'params' => array(
                    'key' => 'alex',
                    'email' => null,
                    'password' => null
                ),
                400
            ),
            array(
                'params' => array(
                    'key' => 'bla',
                    'email' => 'invalidEmail',
                    'password' => 'apassword'
                ),
                400
            )
        );
    }
}
