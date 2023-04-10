<?php

namespace App\Tests;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testIsTrue()
    {
        $user = new User();

        $user->setEmail('user@test.com')
            ->setFirstname('User')
            ->setLastname('Test')
            ->setPassword('p@ssw0rd')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(1);

        $this->assertTrue($user->getEmail() === 'user@test.com');
        $this->assertTrue($user->getFirstname() === 'User');
        $this->assertTrue($user->getLastname() === 'Test');
        $this->assertTrue($user->getPassword() === 'p@ssw0rd');
        $this->assertTrue($user->getRoles() === ['ROLE_USER']);
        $this->assertTrue($user->isIsVerified() === true);
    }

    public function testIsFalse()
    {
        $user = new User();

        $user->setEmail('user@test.com')
            ->setFirstname('User')
            ->setLastname('Test')
            ->setPassword('p@sww0rd')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(1);

        $this->assertFalse($user->getEmail() === 'false@test.com');
        $this->assertFalse($user->getFirstname() === 'False');
        $this->assertFalse($user->getLastname() === 'False');
        $this->assertFalse($user->getPassword() === null);
        $this->assertFalse($user->getRoles() === ['false']);
        $this->assertFalse($user->isIsVerified() === false);
    }

    public function testIsEmpty()
    {
        $user = new User();

        $this->assertEmpty($user->getEmail());
        $this->assertEmpty($user->getFirstname());
        $this->assertEmpty($user->getLastname());
        $this->assertEmpty($user->getPassword());
        $this->assertEmpty($user->getRoles());
        $this->assertEmpty($user->isIsVerified());
    }
}
