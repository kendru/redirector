<?php 
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

namespace Redirector\Controllers;

use Redirector\Models\User;

/**
 * Users controller
 * Manages user state within a session
 */
class Users extends Controller
{
    protected $before_each = array('verifyRole', 'admin');
    /**
     * Renders a login page
     * GET /users/new
     */
    public function doNew()
    {
        $this->render('new');
    }

    public function doCreate()
    {
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

            if ($user->save()) {
                User::authenticate($user->email, $_POST['password']);
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
}

