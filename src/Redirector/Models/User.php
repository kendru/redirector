<?php
/**
 * @package redirector
 * @author Andrew Meredith <ameredit@randomhouse.com>
 */

namespace Redirector\Models;

class User extends \Model
{
    public static $_table = 'users';

    public function save()
    {
        // Validate data
        return parent::save();
    }

}
