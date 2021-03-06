<?php

namespace UserAuth\Utility;

use Cake\Core\App;
use Cake\Core\Configure;
use RuntimeException;

class Config {

    public function __constructor() {
        self::loadPluginConfiguration();
    }

    public static function loadPluginConfiguration() {
        if (file_exists(ROOT . DS . 'config' . DS . 'app_user_auth.php')) {
            Configure::load('app_user_auth');
        } else {
            Configure::load('UserAuth.app_user_auth');
        }
    }

    public static function defaultDatabaseConnection() {
        return Configure::read('UserAuth.database_connection', null);
    }

    public static function defaultUserModel() {
        return Configure::read('UserAuth.userModel', 'UserAuth.Users');
    }

    public static function defaultRoleModel() {
        return Configure::read('UserAuth.roleModel', 'UserAuth.Roles');
    }

    public static function defaultPermissionModel() {
        return Configure::read('UserAuth.permissionModel', 'UserAuth.Permissions');
    }

}
