<?php

namespace UserAuth\Auth;

# CAKEPHP

use Cake\Auth\BaseAuthorize;
use Cake\Http\ServerRequest;
use Cake\Cache\Cache;
use Cake\Log\Log;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\HttpException\UnauthorizedException;
use Cake\Http\Response;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Network\Request;

# OTHERS
use Firebase\JWT\JWT;
use Exception;
use Throwable;

# PLUGIN
use UserAuth\Model\Entity\User;
use UserAuth\Model\Entity\Role;
use UserAuth\Model\Entity\Permission;
use UserAuth\Utility\Config;

class RolesAuthorize extends BaseAuthorize {

    private $_user;
    private $_modelPermissions;
    private $_modelRoles;
    private $_settings;

    /**
     * Checks if a Permission matching plugin, controller and
     * action exists and is allowed to access for the user's
     * role.
     * 'superadmin' user is always authorized
     * 
     * @param type $user
     * @param ServerRequest $request
     * @return type 
     */
    public function authorize($user, ServerRequest $request): bool {

        $this->_user = $user;
        $this->_modelPermissions = TableRegistry::get(Config::defaultPermissionModel());

        $actionRequested = null;
        $roleId = null;

        try {
            $this->_settings = $this->getConfig();
        } catch (Throwable $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        if (isset($this->_settings['authorizeAll']) && $this->_settings['authorizeAll']) {
            return true;
        }

        if (isset($user['role_id'])) {
            $roleId = $user['role_id'];
        }

        if (empty($user) || !isset($user['username']) || !isset($user['role_id'])) {
            $this->_log('No username or role was defined, so you are not authorized');
            return false;
        }

        // superadmin user is cool
        if ($this->userHasRole('superadmin')) {
            return true;
        }

        $actionRequested = [];
        try {
            $actionRequested = [
                'plugin' => $request->getParam('plugin'),
                'controller' => $request->getParam('controller'),
                'action' => $request->getParam('action'),
            ];
        } catch (Throwable $e) {
            
        } catch (Exception $e) {
            
        }

        if (empty($actionRequested)) {
            $this->_log('Action requested does not exist, so you are not authorized');
            return false;
        }

        $this->_log("user: ${user['username']} is trying to access: p(${actionRequested['plugin']}) c(${actionRequested['controller']}) a(${actionRequested['action']}) ");

        if ($roleId) {
            // get permissions for the role
            $permissionsForUserRole = Cache::read(Permission::cacheKeyPrefix . $roleId);
            if ($permissionsForUserRole === false) {
                $permissionsForUserRole = $this->_modelPermissions->getPermissionsForRoleId($roleId);
                try {
                    if ($permissionsForUserRole) {
                        Cache::write(Permission::cacheKeyPrefix . $user['role_id'], $permissionsForUserRole);
                        $this->_log("Caching rules for role_id ${user['role_id']}");
                    }
                } catch (Throwable $e) {
                    
                } catch (Exception $e) {
                    
                }
            } else {
                $this->_log("Getting cached rules for role_id ${user['role_id']}");
            }
            $this->_log(print_r($permissionsForUserRole, true));
        } else {
            $this->_log("No role_id matched for the user.");
        }

        if ($permissionsForUserRole && !empty($permissionsForUserRole)) {
            foreach ($permissionsForUserRole as $permission) {

                $this->_log("checking permission " . $permission['id'] . ' = p(' . $permission['plugin'] . ') c(' . $permission['controller'] . ') a(' . $permission['action'] . ') against p(' . $actionRequested['plugin'] . ') c(' . $actionRequested['controller'] . ') a(' . $actionRequested['action'] . ')');

                if ($permission['plugin'] == '' || $permission['plugin'] == '*' || strtoupper($actionRequested['plugin']) == strtoupper($permission['plugin'])) {

                    $this->_log("plugin matched");
                    if ($permission['controller'] == '' || $permission['controller'] == '*' || strtoupper($actionRequested['controller']) == strtoupper($permission['controller'])) {

                        $this->_log("controller matched");
                        if ($permission['action'] == '' || $permission['action'] == '*' || strtoupper($actionRequested['action']) == strtoupper($permission['action'])) {
                            $this->_log("permission matches, returning true if allowed");
                            return ($permission['allowed']);
                        }
                    }
                }
            }
        }

        $this->_log("no rules matched. user is not allowed ");

        return false;
    }

    protected function userHasRole($roleName) {
        try {
            $this->_modelRoles = TableRegistry::get(Config::defaultRoleModel());
            $role = $this->_modelRoles->getRoleByName($roleName);

            if ($role && isset($role['id'])) {
                if (isset($this->_user['role_id']) && $this->_user['role_id'] == $role['id']) {
                    return true;
                }
            }
        } catch (Throwable $e) {
            
        } catch (Exception $e) {
            
        }
        return false;
    }

    /**
     * Filter debug
     *
     * @param type $var 
     */
    private function _log($var) {
        if ($this->_settings && isset($this->_settings['debug']) && $this->_settings['debug']) {
            try {
                Log::debug($var);
            } catch (Throwable $e) {
                
            } catch (Exception $e) {
                
            }
        }
    }

}
