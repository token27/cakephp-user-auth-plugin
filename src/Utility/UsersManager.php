<?php

namespace UserAuth\Utility;

# CAKEPHP

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

# PLUGIN 

/**
 * Notifier component
 */
class UsersManager {

    protected static $_generalManager = null;

    /**
     * instance
     *
     * The singleton class uses the instance() method to return the instance of the UsersManager.
     *
     * @param null $manager Possible different manager. (Helpfull for testing).
     * @return UsersManager
     */
    public static function instance($manager = null) {
        if ($manager instanceof UsersManager) {
            static::$_generalManager = $manager;
        }
        if (empty(static::$_generalManager)) {
            static::$_generalManager = new UsersManager();
        }
        return static::$_generalManager;
    }

}
