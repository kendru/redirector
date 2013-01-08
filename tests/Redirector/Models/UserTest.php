<?php
require_once 'vendor/autoload.php';
include_once 'tests/helpers/config.php';

use Redirector\Models\User;

/**
 * Tests for App.php
 **/
class UserTest extends PHPUnit_Framework_TestCase
{
    protected $user;

    public static function setUpBeforeClass()
    {
        global $config;
        $db = (object) $config['db'];
        $dsn = "{$db->protocol}:host={$db->host};dbname={$db->database}";
        \ORM::configure($dsn);
        \ORM::configure('username', $db->user);
        \ORM::configure('password', $db->password);
    }
    protected function setUp()
    {
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
        $this->assertTrue($this->user->save());
    }

    protected function tearDown()
    {
        // rollback transaction
    }
}
