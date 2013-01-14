<?php 
/**
 * @package redirector
 * @author Andrew Meredith <ameredith@randomhouse.com>
 */

namespace Redirector\Controllers;

/**
 * Redirects controller
 * Manages redirects
 */
class Redirects extends Controller
{
    protected $before_each = array('verify', 'logged in');
    
    /**
     * Renders a new redirect rule
     * GET /redirects/new
     */
    public function doIndex()
    {
        $redirects = \Model::factory('\Redirector\Models\Redirect')->find_many();

        $user_id = $this->session->currentUser()->id;
        foreach ($redirects as &$rule) {
            $rule->mine = ($rule->users_id === $user_id);
        }
        $admin = $this->session->currentUser()->role === 'admin';

        $this->render('index', array('redirects' => $redirects, 'admin' => $admin));
    }

    /**
     * Renders a new redirect rule
     * GET /redirects/new
     */
    public function doNew()
    {
        $this->render('new');
    }

    public function doShow($id)
    {
        $redirect = \Model::factory('\Redirector\Models\Redirect')->find_one($id);
        $owner =  \Model::factory('\Redirector\Models\User')->find_one($redirect->users_id);

        $env = $this->app->environment();
        $fullurl = $env['slim.url_scheme'] . '://' . $env['HOST'] . '/' . $redirect->alias;

        $this->render('show', array('redirect' => $redirect, 'owner' => $owner, 'fullurl' => $fullurl));
    }

    /**
     * Attempts to save a redirect rule
     * POST /redirects/
     */
    public function doCreate()
    {
        $redirect = \Model::factory('\Redirector\Models\Redirect')->create();

        if (isset($_POST['alias'])
            && isset($_POST['dest'])
            && $this->session->isLoggedIn()
        ) {
            if (strlen($_POST['alias']) > 30) {
                $this->app->flash('error', 'Alias too long (maximum length: 30 characters)');
                $this->redirectWith($this->app->urlFor('new', array('controller' => 'redirects')), 'get');
            }

            if (strpos($_POST['dest'], 'http') !== 0) {
                $this->app->flash('error', 'Destination URL must start with <code>http://</code> or <code>https://</code>');
                $this->redirectWith($this->app->urlFor('new', array('controller' => 'redirects')), 'get');
            }

            $redirect->alias = $_POST['alias'];
            $redirect->dest = $_POST['dest'];
            $redirect->users_id = $this->session->currentUser()->id;
            //$user->is_regex = $_POST['is_regex'];

            try {
                $redirect->save();
                $env = $this->app->environment();
                $fullurl = $env['slim.url_scheme'] . '://' . $env['HOST'] . '/' . $redirect->alias;

                $this->app->flash('notice',
                    'You have succesfully created a redirect rule for: <em><a href="' . $fullurl . '">' . $fullurl . '</a></em>');
                $this->redirectWith($this->app->urlFor('edit', array('controller' => 'redirects', 'id' => $redirect->id)), 'get');
            } catch (\PDOException $e) {
                $this->app->flash('error', 'Redirect rule could not be saved to the database. Perhaps another redirect exists with the same alias.');
                $this->redirectWith('/admin/redirects/new/', 'get');
            }
        } elseif ($this->session->isLoggedIn()) {
            $this->app->flash('error', 'Form missing required data');
            $this->redirectWith('/admin/redirects/new/', 'get');
        } else {
            $this->app->notFound();
        }
    }

    public function doEdit($id)
    {
        $redirect = \Model::factory('\Redirector\Models\Redirect')->find_one($id);
        $owner =  \Model::factory('\Redirector\Models\User')->find_one($redirect->users_id);

        $this->orVerify(
            array(
                'current user'  => array($owner->id),
                'role'          => array('admin'),
            ),
            $this->app->urlFor('index', array('controller' => 'redirects')));

        $env = $this->app->environment();
        $fullurl = $env['slim.url_scheme'] . '://' . $env['HOST'] . '/' . $redirect->alias;

        $this->render('edit', array('redirect' => $redirect, 'owner' => $owner, 'fullurl' => $fullurl));
    }

    public function doDelete($id)
    {
        $redirect = \Model::factory('\Redirector\Models\Redirect')->find_one($id);
        if (!$redirect) $this->app->notFound();

        $owner =  \Model::factory('\Redirector\Models\User')->find_one($redirect->users_id);

        $this->orVerify(
            array(
                'current user'  => array($owner->id),
                'role'          => array('admin'),
            ),
            $this->app->urlFor('index', array('controller' => 'redirects')));

        try {
            $redirect->delete();
            $this->app->flash('notice', 'Redirect deleted');
        } catch(\PDOException $e) {
            $this->app->flash('error', 'Redirect could not be deleted');
        }

        $this->redirectWith('/admin/redirects/', 'get');
    }

    protected function verifyCurrentUser($user_id, $path = '/')
    {
        $current_user = $this->session->currentUser();
        if ($user_id !== $current_user->id) {
            return array(false, 'You are not logged in as the correct user');
        }

        return true;
    }
}

