<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredit@randomhouse.com>
 */

namespace Redirector;

use Redirector\Utils\Inflector;
use \Slim\Slim;
use \Paris;
use \Idiorm;
use Symfony\Component\Yaml\Yaml;

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

    /** @var string the application's execution environment */
    private $env;

    /** @var mixed the application configuration from config/ENV.yml */
    private $config;

    /** @var Session session access object */
    private $session;

    private function __construct()
    {
        $this->session = $session = new Helpers\Session();
        $realpath = dirname(dirname(__DIR__));

        $this->env = isset($_ENV['SLIM_MODE'])
            ? $_ENV['SLIM_MODE']
            : 'dev';
        $this->config = Yaml::parse($realpath . '/config/' . $this->env . '.yml');

        // Configure Idiorm ORM
        $db = (object) $this->config['db'];
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

        $twig = new \Slim\Extras\Views\Twig();
        $twig->twigOptions = array(
            'cache' => $realpath . '/templates/cache'
        );

        $this->router = $app = new Slim(array(
            'view'           => $twig,
            'templates.path' => $realpath . '/templates'
        ));

        $env = $this->router->environment();
        $env['app.realpath'] = $realpath;
        $env['app.pubpath'] = $realpath . '/public';

        $twig->getEnvironment()->addGlobal('flash', $session->flash);
        $twig->getEnvironment()->addGlobal('logged_in', $session->isLoggedIn());
        if ($session->isLoggedIn()) {
            $twig->getEnvironment()->addGlobal('current_user', $session->currentUser());
        }
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
            if ($this->session->isLoggedIn()) {
                $app->render('index.html');
            } else {
                $app->notFound();
            }
        });

        $app->get('/denied/', function() use ($app) {
            echo "Permission denied.";
        })->name('denied');


        $app->get('/:alias/', function($alias) use ($app) {
            $redirect = \Model::factory('\Redirector\Models\Redirect')
                ->where('alias', $alias)
                ->find_one();

            // Redirect found
            if ($redirect) {
                // Update hit counter
                $has_qr = isset($_GET['source']) && 'qr' === strtolower($_GET['source']);
                $redirect->hits = $redirect->hits + 1;
                if ($has_qr) {
                    $redirect->hits_qr = $redirect->hits_qr + 1;
                }
                try {
                    $redirect->save();
                } catch (\ActionNotFoundException $e) {
                    // Do logging
                }

                $app->redirect($redirect->dest);
                // Redirect not found
            } else {
                $app->notFound();
            }
        });


        $app->get('/admin/:controller/', function($controller_name) use ($app) {
            $classname = 'Redirector\\Controllers\\' . ucfirst(Inflector::camelize($controller_name));
            $controller = new $classname($app);
            $controller->setMethod('get');
            try {
                $controller->index();
            } catch (\ActionNotFoundException $e) {
                $app->pass();
            }
        })->name('index');

        $app->get('/admin/:controller/new/', function($controller_name) use ($app) {
            $classname = 'Redirector\\Controllers\\' . ucfirst(Inflector::camelize($controller_name));
            $controller = new $classname($app);
            $controller->setMethod('get');
            try {
                $controller->new();
            } catch (\ActionNotFoundException $e) {
                $app->pass();
            }
        })->name('new');

        $app->get('/admin/:controller/:id/', function($controller_name, $id) use ($app) {
            $classname = 'Redirector\\Controllers\\' . ucfirst(Inflector::camelize($controller_name));
            $controller = new $classname($app);
            $controller->setMethod('get');
            try {
                $controller->show($id);
            } catch (\ActionNotFoundException $e) {
                $app->pass();
            }
        })->name('show')->conditions(array('id' => '\d+'));

        $app->get('/admin/:controller/edit/:id/', function($controller_name, $id) use ($app) {
            $classname = 'Redirector\\Controllers\\' . ucfirst(Inflector::camelize($controller_name));
            $controller = new $classname($app);
            $controller->setMethod('get');
            try {
                $controller->edit($id);
            } catch (\ActionNotFoundException $e) {
                $app->pass();
            }
        })->name('edit')->conditions(array('id' => '\d+'));

        $app->post('/admin/:controller/', function($controller_name) use ($app) {
            $classname = 'Redirector\\Controllers\\' . ucfirst(Inflector::camelize($controller_name));
            $controller = new $classname($app);
            $controller->setMethod('post');
            try {
                $controller->create();
            } catch (\ActionNotFoundException $e) {
                $app->pass();
            }
        })->name('create');

        $app->put('/admin/:controller/:id/', function($controller_name, $id) use ($app) {
            $classname = 'Redirector\\Controllers\\' . ucfirst(Inflector::camelize($controller_name));
            $controller = new $classname($app);
            $controller->setMethod('put');
            try {
                $controller->update($id);
            } catch (\ActionNotFoundException $e) {
                $app->pass();
            }
        })->name('update')->conditions(array('id' => '\d+'));

        $app->delete('/admin/:controller/:id/', function($controller_name, $id) use ($app) {
            $classname = 'Redirector\\Controllers\\' . ucfirst(Inflector::camelize($controller_name));
            $controller = new $classname($app);
            $controller->setMethod('delete');
            try {
                $controller->delete($id);
            } catch (\ActionNotFoundException $e) {
                $app->pass();
            }
        })->name('delete');

        $app->get('/admin/:controller/:action/', function($controller_name, $action_name) use ($app) {
            $classname = 'Redirector\\Controllers\\' . ucfirst(Inflector::camelize($controller_name));
            $controller = new $classname($app);
            $controller->setMethod('get');
            $action_name = Inflector::camelize($action_name);
            try {
                $controller->$action_name();
            } catch (\ActionNotFoundException $e) {
                $app->notFound();
            }
        });

        $app->notFound(function () use ($app) {
            $app->render('404.html');
        });

        $this->router->run();
    }
}
