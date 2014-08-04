<?php

namespace AW\HmacBundle\Tests\Controller;

use AW\HmacBundle\Tests\TestBase;

class UserControllerTest extends TestBase
{
    /**
     * User Service
     * 
     * @var \AW\HmacBundle\Services\UserService
     */
    protected static $userService;
    
    /**
     * User Group
     * 
     * @var \AW\HmacBundle\Entity\UserGroup
     */
    protected static $userGroup;
    
    /**
     * Do stuff before tests
     * 
     * @return void
     */
    public static function setUpBeforeClass()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        self::$userService = $kernel->getContainer()->get('AW_user_service');
        
        // Create a temp group
        self::$userGroup = self::$userService->createUserGroup('temp' . date('dmyhis'));
    }
    
    /**
     * Do stuff after tests
     * 
     * @return void
     */
    public static function tearDownAfterClass()
    {
        
    }
    
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
                    'password' => 'password',
                    'group' => self::$userGroup->getId()
                )
            )
        );
        
        $this->assertEquals(201, $status);
        
        // Remove User then usergroup
        extract(
            $this->doRequest(
                $headers->get('Content-Location'),
                'GET'
            )
        );
        
        extract(
            $this->doRequest(
                '/hmac/user/' . $json['id'],
                'DELETE'
            )
        );
        
        self::$userService->deleteUserGroup(self::$userGroup->getId());
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
            ),
            array(
                'params' => array(
                    'key' => 'bla',
                    'email' => 'valieemail@email.com',
                    'password' => 'apassword'
                ),
                400
            )
        );
    }
}
