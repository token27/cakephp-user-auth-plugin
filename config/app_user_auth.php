<?php

/**
 * This file configures default behavior for all workers
 *
 * To modify these parameters, copy this file into your own CakePHP config directory or copy the array into your existing file.
 */
return [
    'UserAuth' => [
        /**
         *  Determine whether logging is enabled
         */
        'log' => true,
        /**
         *  The Users Model
         */
        'userModel' => 'UserAuth.Users',
        /**
         *  The Roles Model
         */
        'roleModel' => 'UserAuth.Roles',
        /**
         *  The Permissions Model
         */
        'permissionModel' => 'UserAuth.Permissions',
        /**
         * The database connection
         */
        'database_connection' => null,
    ],
];
