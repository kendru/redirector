<?php
require_once 'vendor/autoload.php';

use Redirector\Models\User;
use Symfony\Component\Yaml\Yaml;
    
/**
 * Tests for App.php
 **/
class UserTest extends PHPUnit_Framework_TestCase
{
    protected $user;

    protected function setUp()
    {
        putenv('DB=mysql');
        putenv('APP_ENV=test');
        $this->user = \Model::factory('\Redirector\Models\User')->create();

    }

    public function testInheritsFromModel()
    {
        $this->assertTrue(
            method_exists(new User(), 'set_orm')
        );
    }

    public function testAcceptsValidParams()
    {
        $this->user->fname = "John";
        $this->user->lname = "Smith";
        $this->user->email = "jsmith@example.com";
        $this->user->password = "secret";
        $this->user->password_confirmation = "secret";
        $this->assertTrue($user->save());
    }

    protected function tearDown()
    {
        // rollback transaction
    }
}
