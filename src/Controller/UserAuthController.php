<?php

declare(strict_types=1);

namespace UserAuth\Controller;

# CAKEPHP

use Cake\Core\App;
use Cake\Event\Event;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use App\Controller\AppController;
use Cake\HttpException\NotFoundException;
use Throwable;
use Exception;

# PLUGIN
use UserAuth\Utility\Config;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class UserAuthController extends AppController {

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void {

        parent::initialize();

        $this->loadComponent('RequestHandler');

        /**
         * Auth Component
         */
        if (!$this->_isComponentLoaded()) {
            $this->loadComponent('Auth', [
                'loginAction' => [
//                'plugin' => 'UserAuth',
                    'controller' => 'Home',
                    'action' => 'notallowed'
                ],
                'loginRedirect' => [
//                'plugin' => 'UserAuth',
                    'controller' => 'Home',
                    'action' => 'welcome'
                ],
                'logoutRedirect' => [
//                'plugin' => 'UserAuth',
                    'controller' => 'Home',
                    'action' => 'index'
                ],
                'authorize' => [
                    'UserAuth.Roles' => [
                        'debug' => true,
                        'authorizeAll' => false,
                    ]
                ],
                'authenticate' => [
                    'Form' => [
                        'userModel' => Config::defaultUserModel(),
                        'scope' => [
                            Config::defaultUserModel() . '.status' => 1
                        ]
                    ],
                    'UserAuth.Token' => [
                        'userModel' => Config::defaultUserModel(),
                        'parameter' => 'token',
                        'scope' => [
                            Config::defaultUserModel() . '.status' => 1
                        ],
                        'fields' => [
                            'username' => 'id'
                        ],
                        'queryDatasource' => true,
                        'unauthenticatedException' => null // with null redirect is activated
                    ]
                ],
                'unauthorizedRedirect' => true,
                'checkAuthIn' => 'Controller.initialize'
            ]);
        }
    }

    private function _isComponentLoaded() {
        try {
            $components = $this->components();
            if (!empty($components)) {
                if (isset($components->Auth)) {
                    return true;
                }
            }
        } catch (Throwable $t) {
            
        } catch (Exception $e) {
            
        }
        return false;
    }

    /**
     * 
     * @param \Cake\Event\EventInterface $event
     */
    public function beforeFilter(\Cake\Event\EventInterface $event) {
        parent::beforeFilter($event);
        $this->Auth->startup($event);
    }

    public function isSuperadmin() {
        $role = $this->getUserRole();
        if ($role && !empty($role)) {
            if (isset($role['name']) && $role['name'] == "superadmin") {
                return true;
            }
        }
        return false;
    }

    public function isAdmin() {
        $role = $this->getUserRole();
        if ($role && !empty($role)) {
            if (isset($role['name']) && $role['name'] == "admin") {
                return true;
            }
        }
        return false;
    }

    public function getUserRole() {
        $role = [];
        $user = null;
        try {
            $user = $this->Auth->user();
        } catch (Throwable $t) {
            $user = null;
        } catch (Exception $e) {
            $user = null;
        }
        if ($user && !empty($user)) {
            if (isset($user['role_id']) && $user['role_id'] && $user['role_id'] != "") {
                try {
                    $role_database = $this->Roles->getRole($user['role_id']);
                    if ($role_database) {
                        $role = $role_database;
                    }
                } catch (Throwable $t) {
                    
                } catch (Exception $e) {
                    
                }
            }
        }
        return $role;
    }

}
