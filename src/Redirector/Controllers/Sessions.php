<?php 
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

namespace Redirector\Controllers;

use Redirector\Models\User;

/**
 * Sessions controller
 * Manages user state within a session
 */
class Sessions extends Controller
{
     /**
      * Renders a login page
      * GET /session/new
      */
     public function doNew()
     {
         $this->render('new');
     }

     public function doCreate()
     {
         if (isset($_POST['email'])
             && isset($_POST['password'])
             && User::authenticate($_POST['email'], $_POST['password'])
         ) {
             $this->app->flash('success', 'You have succesfully logged in');
             $this->redirectWith($this->app->urlFor('index', array('controller' => 'redirects')), 'get');
         } else {
             $this->app->flash('error', 'Invalid username or password');
             $this->redirectWith($this->app->urlFor('new', array('controller' => 'sessions')), 'get');
         }
     } 

     public function doDestroy()
     {
         User::logOut();
         $this->app->flash('error', 'You have succesfully logged out');
         $this->redirectWith($this->app->urlFor('new', array('controller' => 'sessions')), 'get');
     }
}

