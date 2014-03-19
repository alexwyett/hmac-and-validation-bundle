<?php

namespace AW\HmacBundle\Tests\Controller;

use AW\HmacBundle\Tests\TestBase;

class ApiUserControllerTest extends TestBase
{
    /**
     * Test the ping endpoint
     * 
     * @return void
     */
    public function testCreateApiUser()
    {
        extract(
            $this->doRequest(
                '/hmac/apiuser',
                'POST',
                array(
                    'key' => 'alex',
                    'email' => 'alex@carltonsoftware.co.uk'
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
                '/hmac/apiuser',
                'POST',
                $params
            )
        );
        
        $this->assertEquals($status, $expectedStatus);
    }
    
    /**
     * Test the read function
     * 
     * @param integer $count Expected number in array
     * 
     * @return void
     */
    public function testListApiUsers($count = 1)
    {
        extract(
            $this->doRequest(
                '/hmac/apiuser',
                'GET',
                array(),
                false
            )
        );
        
        $this->assertEquals($count, count($json));
    }
    
    /**
     * Test view individual list function
     * 
     * @param string  $email   Expected email
     * @param array   $roles   Expected roles
     * @param boolean $enabled Expected visibility flag
     * 
     * @return void
     */
    public function testListApiUser(
        $email = 'alex@carltonsoftware.co.uk',
        $roles = null,
        $enabled = true
    ) {
        if ($roles == null) {
            $roles = array('USER');
        }
        
        extract(
            $this->doRequest(
                '/hmac/apiuser/alex',
                'GET',
                array(),
                false
            )
        );
        
        $this->assertEquals(200, $status);
        $this->assertEquals('alex', $json->key);
        $this->assertEquals($email, $json->email);
        $this->assertEquals($roles, $json->roles);
        $this->assertEquals($enabled, $json->enabled);
    }
    
    /**
     * Test the update route
     * 
     * @return void
     */
    public function testUpdateApiUser()
    {
        extract(
            $this->doRequest(
                '/hmac/apiuser/alex',
                'PUT',
                array(
                    'email' => 'alex@tocc.co.uk',
                    'secret' => 'newsecret'
                )
            )
        );
        
        $this->testListApiUser('alex@tocc.co.uk', array('USER'), true);
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
    public function testUpdateApiUserException($params, $expectedStatus)
    {
        extract(
            $this->doRequest(
                '/hmac/apiuser/alex',
                'PUT',
                $params
            )
        );
        
        $this->assertEquals($status, $expectedStatus);
    }
    
    /**
     * Test updating the user role
     * 
     * @return void
     */
    public function testPromoteApiUser()
    {
        extract(
            $this->doRequest(
                '/hmac/apiuser/alex/role/ADMIN',
                'PUT'
            )
        );
        
        $this->testListApiUser('alex@tocc.co.uk', array('USER', 'ADMIN'), true);
        
        extract(
            $this->doRequest(
                '/hmac/apiuser/alex/role/ADMIN',
                'DELETE'
            )
        );
        
        $this->testListApiUser('alex@tocc.co.uk', array('USER'), true);
    }
    
    /**
     * Remove an apiuser
     * 
     * @return void
     */
    public function testDeleteApiUser()
    {
        extract(
            $this->doRequest(
                '/hmac/apiuser/alex',
                'DELETE'
            )
        );
        
        $this->testListApiUsers(0);
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
                    'email' => null
                ),
                400
            ),
            array(
                'params' => array(
                    'key' => 'alex',
                    'email' => null
                ),
                400
            ),
            array(
                'params' => array(
                    'key' => 'bla',
                    'email' => 'invalidEmail'
                ),
                400
            )
        );
    }
}
