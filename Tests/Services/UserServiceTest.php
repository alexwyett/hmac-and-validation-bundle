<?php

namespace AW\HmacBundle\Tests\Services;

use AW\HmacBundle\Tests\TestBase;

/**
 * Test the user service
 *
 * @category  Tests
 * @package   AW
 * @author    Alex Wyett <alex@wyett.co.uk>
 * @copyright 2014 Alex Wyett
 * @license   All rights reserved
 * @link      http://www.wyett.co.uk
 */
class UserServiceTest extends TestBase
{
    /**
     * User Service
     * 
     * @var \AW\HmacBundle\Services\UserService
     */
    protected static $userService;
    
    public static function setUpBeforeClass()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        self::$userService = $kernel->getContainer()->get('AW_user_service');
    }
    
    /**
     * Test the user crud
     * 
     * @return void
     */
    public function testUserCrud()
    {
        // Create a temp group
        $group = self::$userService->createUserGroup('temp' . date('dmyhis'));
        
        // Create a user
        $user = self::$userService->createUser(
            'test',
            'test@test.com',
            'test',
            $group
        );
        
        $this->assertEquals('test', $user->getUsername());
        $this->assertEquals('test@test.com', $user->getEmail());
        $this->assertEquals('test', $user->getPassword());
        $this->assertFalse($user->isEnabled());
        $this->assertTrue($user->getGroup() == $group);
        
        // Enable User
        $user = self::$userService->toggleUser($user->getId(), true);
        $this->assertTrue($user->isEnabled());
        
        // Update
        $user = self::$userService->updateUser(
            $user->getId(),
            array(
                'username' => 'test2',
                'email' => 'test@test2.com',
                'password' => 'test2'
            )
        );
        
        $this->assertEquals('test2', $user->getUsername());
        $this->assertEquals('test@test2.com', $user->getEmail());
        $this->assertEquals('test2', $user->getPassword());
        
        // Test the login function
        $this->assertEquals(
            $user,
            self::$userService->getUserByLogin(
                $user->getUsername(),
                $user->getPassword(),
                $group
            )
        );
        
        // Delete the user
        $this->assertTrue(
            self::$userService->deleteUser($user->getId())
        );
        
        // Delete the group
        $this->assertTrue(
            self::$userService->deleteUserGroup($group->getId())
        );
    }
}
