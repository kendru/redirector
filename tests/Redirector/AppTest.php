<?php
require_once 'vendor/autoload.php';

use Redirector\App;
    
/**
 * Tests for App.php
 **/
class AppTest extends PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = new App();
    }

    public function tearDown()
    {
        unset($this->app);
    }

    public function testRootPathSettable()
    {
        $this->app->setRootPath('/tmp');
        $this->assertEquals($this->app->getRootPath(), '/tmp');
    }

    public function testRootPathStripsTrailingSlash()
    {
        $this->app->setRootPath('/tmp/');
        $this->assertEquals($this->app->getRootPath(), '/tmp');
    }
}
