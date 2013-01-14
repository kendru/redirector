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
        // Configure Idiorm ORM
        global $config;
        $db = (object) $config['db'];
        $dsn = (in_array($db->protocol, array('mysql', 'pgsql')))
            ? "{$db->protocol}:host={$db->host};dbname={$db->database}"
            : "{$db->protocol}:{$db->database}"; // Probably SQLite
        \ORM::configure($dsn);

        if (isset($db->user)) {
            \ORM::configure('username', $db->user);
        }
        if (isset($db->password)) {
            \ORM::configure('password', $db->password);
        }

        $setup = file_get_contents('tests/helpers/create_database.sql');
        $setup .= file_get_contents('tests/helpers/user_factory.sql');

        ORM::for_table('dummy')->raw_query($setup);
        echo $setup;
    }

    public static function tearDownAfterClass()
    {

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
