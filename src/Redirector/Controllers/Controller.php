<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

namespace Redirector\Controllers;

use Redirector\Helpers\Session;
use Redirector\Exceptions\ActionNotFoundException;
use Redirector\Exceptions\InvalidVerificationException;
use Redirector\Exceptions\ExpectationNotMetException;

/**
 * Controller base class
 **/
class Controller
{
    
    /** @var Slim the Slim framework object, used for rendering */
    protected $app;

    /** @var string the HTTP request method of the current request */
    protected $method = 'get';  

    /** @var Session the session "wrapper" object */
    protected $session;

    public function __construct($app)
    {
        $this->session = new Session();
        $this->app = $app;
    }

    /**
     * Gets the HTTP request method
     *
     * @return string the HTTP method of the current request
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets the HTTP method for the current request
     *
     * @param string $method the HTTP method. Valid values: get, post, put, delete
     * @return void
     * @throws \InvalidArgumentException on invalid method supplied
     */
    public function setMethod($method)
    {
        $method = strtolower($method);
        $valid_methods = array('get', 'post', 'put', 'delete');
        
        if (in_array($method, $valid_methods)) {
            $this->method = $method;
        } else {
            throw new \InvalidArgumentException('Method, "' . $method . '" is not a valid HTTP verb');
        }
    }

    /**
     * Renders the desired template
     * Sets the base template path and defers to the main application renderer
     *
     * @param string $template the name of the template to render
     * @param array $args the array of arguments to supply to the template
     */
    protected function render($template, $args = array())
    {
        $this_controller = preg_replace('/.*\\\\(\w+)$/', '$1', get_called_class());

        if (stripos('.html', $template) === false) {
            $template .= '.html';
        }

        $template = strtolower($this_controller) . '/' . $template;
        
        return $this->app->render($template, $args);
    }

    protected function redirectWith($path, $method)
    {
        $env = $this->app->environment();
        //TODO: ensure that the method is valid
        $env['REQUEST_METHOD'] = strtoupper($method);
        $this->app->redirect($path);
    }

// TODO move the "verify" functionality to a set of Authorization classes
    protected function verify($method, $args = array(), $path = '/')
    {
        $log = $this->app->getLog();

        try {
            $this->tryVerify($method, $args);
        } catch (InvalidVerificationException $e) {
            $log->warn($e->getMessage());
        } catch (ExpectationNotMetException $e) {
            $this->app->flash('error', $e->getMessage());
            $this->redirectWith($path, 'get');
        } catch (\Exception $e) {
            $log->warn('Unexpected exception thrown: ' . $e->getMessage());
        }
    }

    // TODO
    protected function orVerify($args_as_array, $path = '/')
    {
        if (!is_array($args_as_array) || empty($args_as_array)) {
            throw new \InvalidArgumentException('Expected an array of validator methods => arguments');
        }

        $messages = array();
        $log = $this->app->getLog();

        foreach($args_as_array as $method => $args) {
            if (!is_array($args)) {
                throw new \InvalidArgumentException('Expected arguments to be an array.');
            }

            try {
                $this->tryVerify($method, $args);
                return true; // Passes as soon as the first verification passes
            } catch (InvalidVerificationException $e) {
                $log->warn($e->getMessage());
            } catch (ExpectationNotMetException $e) {
                $messages[] = $e->getMessage();
            } catch (\Exception $e) {
                $log->warn('Unexpected exception thrown: ' . $e->getMessage());
            }
        }

        $this->app->flash('error', implode("<br />\n", $messages));
        $this->redirectWith($path, 'get');
        return false;
    }

    // TODO
    protected function andVerify($args_as_array)
    {
        if (!is_array($args_as_array) || empty($args_as_array)) {
            throw new \InvalidArgumentException('Expected an array of validator methods => arguments');
        }

        $messages = array();
        $log = $this->app->getLog();

        foreach($args_as_array as $method => $args) {
            if (!is_array($args)) {
                throw new \InvalidArgumentException('Expected arguments to be an array.');
            }

            try {
                $this->tryVerify($method, $args);
            } catch (InvalidVerificationException $e) {
                $log->warn($e->getMessage());
            } catch (ExpectationNotMetException $e) {
                $messages[] = $e->getMessage();
            } catch (\Exception $e) {
                $log->warn('Unexpected exception thrown: ' . $e->getMessage());
            }
        }

        if (!empty($messages)) {
            $this->app->flash('error', implode("<br />\n", $messages));
            $this->redirectWith($path, 'get');
            return false;
        }

        return true;
    }

    /**
     * Calls a defined verification method
     *
     * Calls the verification method specified by $method
     * with the $args given. The method silently returns
     * if validation passes and throws an exception if
     * validation fails.
     *
     * @throws \Redirector\Exceptions\InvalidVerificationException
     * @throws \Redirector\Exceptions\ExpectationNotMetException
     * @param string $method the method to call, e.g. 'logged in' calls
     * $this->verifyLoggedIn()
     * @param $args array the methods to pass to the verification method
     * @return void
     */
    private function tryVerify($method, $args = array())
    {
        $real_method = 'verify' . ucfirst(\Redirector\Utils\Inflector::camelize($method));
        if (!method_exists($this, $real_method)) {
            throw new InvalidVerificationException (
                'The verification methd that you are attempting to use appears to be invalid');
        }
        
        $result = call_user_func_array(array($this, $real_method), $args);
        $status = (is_array($result) && isset($result[0]))
            ? (boolean) $result[0]
            : (boolean) $result;
        $message = (is_array($result) && isset($result[1]))
            ? $result[1]
            : 'Expectation not met';

        if (!$status) {
            throw new ExpectationNotMetException($message);
        }
    }

    protected function verifyLoggedIn($path = '/admin/sessions/new/')
    {
        if (!$this->session->isLoggedIn()) {
            return array(false, 'You must be logged-in to do that');
        }

        return true;
    }

    protected function verifyRole($role = 'admin', $path = '/')
    {
        $this->verify('logged in');
        if ($this->session->currentUser()->role !== $role) {
            return array(false, 'You do not have sufficient access to do that. You need to be <stong>' . $role . '</strong>, but you are only <small>' . $user_role . '</small>');
        }
        return true;
    }

    public function __call($method, $args)
    {
        if (isset($this->before_each)) {
            if (!is_array($this->before_each)
                && method_exists($this, $this->before_each)
            ) {
                $pre = $this->before_each;
                $this->$pre();
            } elseif (method_exists($this, $this->before_each[0])) {
                $pre = $this->before_each[0];
                $this->$pre($this->before_each[1]);
            }
        }

        $action_method_name = 'do' . ucfirst($method);
        if (method_exists($this, $action_method_name)) {
            if (isset($args) && isset($args[0])) {
                $this->$action_method_name($args[0]);
            } else {
                $this->$action_method_name();
            }
        } else {
            throw new ActionNotFoundException("Action, '$action_method_name' does not exist");
        }
    }
}
