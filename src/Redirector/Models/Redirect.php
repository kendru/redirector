<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredit@randomhouse.com>
 */

namespace Redirector\Models;

use Redirector\Helpers\Session;
use Slim\Slim;

class Redirect extends \Model
{
    public static $_table = 'redirects';

    public function user()
    {
        return $this->belongs_to('User');
    }
}
