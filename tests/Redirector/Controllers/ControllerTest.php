<?php
require_once 'vendor/autoload.php';
include_once 'tests/helpers/config.php';
include_once 'tests/helpers/classes/FakeSession.php';

use Redirector\Controllers\Controller;

/**
 * Test for Sessions.php
 **/
class ControllerTest extends PHPUnit_Framework_TestCase
{
    protected $controller;

    public function setUp()
    {
        $this->controller = new Controller(new \StdClass(), new FakeSession());
    }

    public function testRequestMethodDefaultsToGet()
    {
        $this->assertEquals($this->controller->getMethod(), 'get');
    }
    
    public function testCanSetRequestMethod()
    {
        $this->controller->setMethod('post');

        $this->assertEquals($this->controller->getMethod(), 'post');
    }

    public function testThrowsExceptionOnInvalidRequestMethod()
    {
        try {
            $this->controller->setMethod('YUMMY BACON!!!');
        } catch (\InvalidArgumentException $expected) {
            return;
        }
        
        $this->fail('The expected exception has not been raised');
    }
}

