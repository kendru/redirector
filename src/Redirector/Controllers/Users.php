<?php 
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

namespace Redirector\Controllers;

use Redirector\Models\User;
use \Paris;

/**
 * Users controller
 * Manages user state within a session
 */
class Users extends Controller
{
    /**
     * Renders a login page
     * GET /users/new
     */
    public function doNew()
    {
        $this->orVerify(
            array(
                'number of users'  => array(0),
                'role'          => array('admin')
            ),
            $this->app->urlFor('denied'));

        $this->render('new');
    }

    public function doCreate()
    {
        $this->orVerify(
            array(
                'number of users'  => array(0),
                'role'          => array('admin')
            ),
            $this->app->urlFor('denied'));

        $user = \Model::factory('\Redirector\Models\User')->create();

        if (isset($_POST['email'])
            && isset($_POST['password'])
            && isset($_POST['fname'])
            && isset($_POST['lname'])
        ) {
            $user->email = $_POST['email'];
            $user->password = $_POST['password'];
            $user->fname = $_POST['fname'];
            $user->lname = $_POST['lname'];

            // Make the first user an admin
            if(0 === \Model::factory('\Redirector\Models\User')->count()) {
                $user->role = 'admin';
            }
           

            if ($user->save()) {
                if (!$this->session->isLoggedIn()) {
                    User::authenticate($user->email, $_POST['password']);
                }

                $this->app->flash('notice', 'You have succesfully created an account');
                $this->redirectWith(
                    $this->app->urlFor('index', array('controller' => 'redirects')), 'get');
            } else {
                $this->app->flash('error', 'User could not be saved to the database');
                $this->redirectWith(
                    $this->app->urlFor('new', array('controller' => 'users')), 'get');
            }
        } else {
            $this->app->flash('error', 'Form missing required data');
            $this->redirectWith(
                $this->app->urlFor('new', array('controller' => 'users')), 'get');
        }
    }

    protected function verifyNumberOfUsers($num)
    {
        $count = \Model::factory('\Redirector\Models\User')->count();
        return ($count === $num)
            ? true
            : array(false, "Expected $num users. Got $count.");
    }
}

