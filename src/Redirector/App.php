<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredit@randomhouse.com>
 */

namespace Redirector;

use \Slim\Slim;

/**
 * Application point of entry
 **/
class App
{
    /** @var Slim the application route handler */
    private $router;

    /** @var App the singleton instance */
    private static $instance;

    /** @var string the root path of the project */
    private $root_path;

    private function __construct()
    {
        $twig = new \Slim\Extras\Views\Twig();
        $twig->twigOptions = array(
            'cache' => dirname(dirname(__DIR__)) . '/templates/cache'
        );

        $this->router = new Slim(array(
            'view'           => $twig,
            'templates.path' => dirname(dirname(__DIR__)) . '/templates'
        ));
    }

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function run()
    {
        $app = $this->router;

        $app->get('/', function() use ($app) {
            $app->render('index.html');
        });

        $app->get('/:controller/:action', function($controller_name, $action_name) use ($app) {
            $classname = 'Redirector\\Controllers\\' . ucfirst($this->camelize($controller_name));
            $controller = new $classname($app);
            $controller->setMethod('get');
            $action_name = $this->camelize($action_name);
            $controller->$action_name();
        });

        $this->router->run();
    }

    private function camelize($str)
    {
        return lcfirst(str_replace(' ', '', ucwords(preg_replace('/[^a-zA-z0-9]/', ' ', $str))));
    }
}
