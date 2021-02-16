<?php

declare(strict_types=1);

namespace UserAuth\Controller;

# CAKEPHP

use Cake\Network\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Cake\Event\Event;
use Cake\HttpException\UnauthorizedException;
use Cake\Utility\Security;
use Cake\Http\ServerRequest;
use Cake\I18n\Time;

# OTHERS
use Firebase\JWT\JWT;
use Throwable;
use Exception;

# PLUGIN
use UserAuth\Controller\UserAuthController;
use UserAuth\Utility\Config;

class UsersController extends UserAuthController {

    public function initialize(): void {
        parent::initialize();
        $this->Auth->allow(['index', 'list', 'login', 'token']);
        try {
            $this->loadModel(Config::defaultUserModel());
            $this->loadModel(Config::defaultRoleModel());
            $this->loadModel(Config::defaultPermissionModel());
        } catch (Throwable $t) {
            
        } catch (Exception $e) {
            
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index() {

        $this->response->withType('application/json');

        $response = [
            'status' => 0,
            'message' => __('Silence is Golden.'),
        ];

        $this->set([
            'response' => $response,
            '_serialize' => 'response',
        ]);

        $this->RequestHandler->renderAs($this, 'json');
    }

    /**
     * Login User 
     */
    public function login() {

        $this->response->withType('application/json');

        $response = [
            'status' => 0,
            'message' => __('Silence is golden.'),
        ];

        $role_name = "-";
        $role_id = null;

        $user = null;
        $username = null;
        $password = null;
        $key = null;
        $token = null;

        $exception = false;

        if ($this->request->is('post') || $this->request->is('ajax')) {
            try {
                $user = $this->Auth->identify();
            } catch (Exception $e) {
                $exception = true;
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $e->getMessage(),
                ];
            }
        }

        if ($user) {

            try {

                $this->Auth->setUser($user);

                $key = Security::getSalt();
                $iat = time();
                $expire_at = time() + 86400;
//                $role_id = isset($user['role_id']) ? $user['role_id'] : null;
//                if ($role_id && $role_id != "") {
//                    $role_database = $this->Roles->getRole($role_id);
//                    if ($role_database) {
//                        $role_name = $role_database['name'];
//                    }
//                }

                $token_data = [
                    'alg' => 'HS256',
                    'id' => $user['id'],
                    'username' => $user['username'],
//                    'role' => $role_name,
                    'sub' => $user['id'],
                    'iat' => $iat,
                    'exp' => $expire_at,
                ];


                $token = JWT::encode($token_data, $key);

                $response = [
                    'status' => 1,
                    'message' => __('Ok.'),
                    'success' => __('Login Successfully.'),
                    'login' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'token' => $token,
                    ],
                ];

                $this->Users->updateUserToken($user['id'], $token, $expire_at);
            } catch (Throwable $t) {
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $t->getMessage(),
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $e->getMessage(),
                ];
            }
        } else {
            if (!$exception)
                $response = [
                    'status' => 0,
                    'message' => __('Your username or password is incorrect.'),
                    'error' => __('Your username or password is incorrect.'),
                ];
        }

        $this->set([
            'response' => $response,
            '_serialize' => 'response',
        ]);

        $this->RequestHandler->renderAs($this, 'json');
    }

    /**
     * 
     */
    public function token() {

        $this->response->withType('application/json');

        $response = [
            'status' => 0,
            'message' => __('Silence is golden.'),
        ];

        $token = null;
        $payload = null;

        if ($this->request->is('post') || $this->request->is('ajax')) {
            try {
                $data = $this->request->getData();
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $e->getMessage(),
                ];
            }
            if ($data && !empty($data)) {
                if (isset($data['token']) && $data['token'] != '') {
                    $token = trim($data['token']);
                }
            }
        }

        if ($token) {
            try {
                $payload = JWT::decode(
                                $token,
                                Security::getSalt(),
                                ['HS256']
                );
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $e->getMessage(),
                ];
            }
        } else {
            
        }

        if ($payload) {
            $response = [
                'status' => 1,
                'message' => __('Token successfully.'),
                'payload' => $payload,
            ];
        }

        $this->set([
            'response' => $response,
            '_serialize' => 'response',
        ]);

        $this->RequestHandler->renderAs($this, 'json');
    }

    /**
     * Logout User 
     */
    public function logout() {

        $this->response->withType('application/json');

        $response = [
            'status' => 0,
            'message' => __('Silence is Golden.'),
        ];

        try {
            $this->Auth->logout();
            $response = [
                'status' => 1,
                'message' => __('Ok.'),
                'success' => __('Logout Successfully.'),
            ];
        } catch (Exception $e) {
            $response = [
                'status' => 0,
                'message' => __('Something wrong, please try again later...'),
                'error' => $e->getMessage(),
            ];
        }

        $this->set([
            'response' => $response,
            '_serialize' => 'response',
        ]);

        $this->RequestHandler->renderAs($this, 'json');
    }

    /**
     * 
     * 
     *      ONLY FOR 
     *      SUPERADMIN
     * 
     */

    /**
     * List method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function list() {

        $this->response->withType('application/json');

        $response = [
            'status' => 0,
            'message' => __('Silence is Golden.'),
        ];

        $limit = 10;
        $page = 1;
        $options_users = [];

        $role_id = null;
        $status = null;

        $users = [];
        if ($this->request->is('post') || $this->request->is('ajax')) {
            try {
                if ($this->request->getData('role_id')) {
                    $role_id = $this->request->getData('role_id');
                }
                if ($this->request->getData('status') != null) {
                    $status = intval($this->request->getData('status'));
                }
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $e->getMessage(),
                ];
            }
        }

        if ($this->request->getQuery('role_id')) {
            $role_id = $this->request->getQuery('role_id');
        }
        if ($this->request->getQuery('status') != null) {
            $status = intval($this->request->getQuery('status'));
        }

        if ($role_id) {
            $options_users['role_id'] = $role_id;
        }
        if ($status !== null) {
            $options_users['status'] = $status;
        }

        if ($this->isSuperadmin()) {
            $users_database = $this->Users->getUsers($options_users, $limit, $page);
            if (!empty($users_database['results'])) {
                $response = [
                    'status' => 1,
                    'message' => __('Ok.'),
                ];
            } else {
                $response['message'] = __('No users found.');
            }
            $response = array_merge($response, $users_database);
        } else {
            $response['message'] = __('Your not allowed to access here.');
        }

        $this->set([
            'response' => $response,
            '_serialize' => 'response',
        ]);
        $this->RequestHandler->renderAs($this, 'json');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function add() {

        $this->response->withType('application/json');

        $response = [
            'status' => 0,
            'message' => __('Silence is Golden.'),
        ];

        $user = [];
        $role_id = null;
        $status = 1;

        $username = "";
        $password = "";

        if ($this->request->is('post') || $this->request->is('ajax')) {
            try {
                if ($this->request->getData('role_id')) {
                    $role_id = $this->request->getData('role_id');
                }

                if ($this->request->getData('username')) {
                    $username = $this->request->getData('username');
                }
                if ($this->request->getData('password')) {
                    $password = $this->request->getData('password');
                }

                if ($this->request->getData('status') != null) {
                    $status = intval($this->request->getData('status'));
                }
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $e->getMessage(),
                ];
            }
        }

        if ($this->request->getQuery('role_id')) {
            $role_id = $this->request->getQuery('role_id');
        }
        if ($this->request->getQuery('username')) {
            $username = $this->request->getQuery('username');
        }
        if ($this->request->getQuery('password')) {
            $password = $this->request->getQuery('password');
        }

        if ($this->request->getQuery('status') != null) {
            $status = intval($this->request->getQuery('status'));
        }


        if ($this->isSuperadmin()) {

            if ($role_id) {
                if ($username && $password) {
                    $user_database = $this->Users->getUser($username);
                    if (!$user_database) {
                        $role_database = $this->Roles->getRole($role_id);
                        if ($role_database) {
                            $user_data = [
                                'role_id' => $role_database['id'],
                                'username' => $username,
                                'password' => $password,
                                'status' => $status
                            ];
                            if ($role_database['name'] == "superadmin") {
                                $user_data['is_superuser'] = 1;
                            }
                            try {
                                $user_database_saved = $this->Users->addUser($user_data);
                                if ($user_database_saved['status'] == 1) {
                                    $response = [
                                        'status' => 1,
                                        'message' => __('Ok.'),
                                        'user_id' => $user_database_saved['user']['id'],
                                    ];
                                }
                            } catch (Throwable $t) {
                                $response = [
                                    'status' => 0,
                                    'message' => __('Something wrong, please try again later...'),
                                    'error' => $t->getMessage(),
                                ];
                            } catch (Exception $e) {
                                $response = [
                                    'status' => 0,
                                    'message' => __('Something wrong, please try again later...'),
                                    'error' => $e->getMessage(),
                                ];
                            }
                        } else {
                            $response['message'] = __('Role not found.');
                        }
                    } else {
                        $response['message'] = __('The username already exists.');
                    }
                } else {
                    $response['message'] = __('The username and password cannot be empty.');
                }
            } else {
                $response['message'] = __('The role cannot be empty.');
            }
            if (!empty($user)) {
                $response = [
                    'status' => 1,
                    'message' => __('Ok.'),
                    'user' => $user
                ];
            }
        } else {
            $response['message'] = __('Your not allowed to access here.');
        }

        $this->set([
            'response' => $response,
            '_serialize' => 'response',
        ]);
        $this->RequestHandler->renderAs($this, 'json');
    }

    /**
     * Remove method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function remove() {

        $this->response->withType('application/json');

        $response = [
            'status' => 0,
            'message' => __('No accounts found.'),
        ];

        $user = [];

        $username = "";


        if ($this->request->is('post') || $this->request->is('ajax')) {
            try {

                if ($this->request->getData('username')) {
                    $username = $this->request->getData('username');
                }
            } catch (Throwable $t) {
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $t->getMessage(),
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $e->getMessage(),
                ];
            }
        }

        if ($this->request->getQuery('username')) {
            $username = $this->request->getQuery('username');
        }


        if ($this->isSuperadmin()) {

            $user_database = $this->Users->getUser($username);
            if ($user_database) {
                $can_be_deleted = true;

                if (isset($user_database['is_superuser']) && $user_database['is_superuser'] == 1) {
                    $can_be_deleted = false;
                } else {
                    if (isset($user_database['role_id']) && $user_database['role_id'] && $user_database['role_id'] != "") {
                        $role_database = $this->Roles->getRole($user_database['role_id']);
                        if ($role_database) {
                            if ($role_database['name'] == "superadmin") {
                                $can_be_deleted = false;
                            }
                        }
                    }
                }
                if ($can_be_deleted) {

                    try {
                        $user_database_deleted = $this->Users->deleteId($user_database['id']);
                        if ($user_database_deleted) {
                            $response = [
                                'status' => 1,
                                'message' => __('Ok.'),
                                'user_id' => $user_database['id'],
                            ];
                        } else {
                            $response['message'] = __('Error while try to remove the username.');
                            $response['user_id'] = $user_database['id'];
                        }
                    } catch (Throwable $t) {
                        $response = [
                            'status' => 0,
                            'message' => __('Something wrong, please try again later...'),
                            'error' => $t->getMessage(),
                        ];
                    } catch (Exception $e) {
                        $response = [
                            'status' => 0,
                            'message' => __('Something wrong, please try again later...'),
                            'error' => $e->getMessage(),
                        ];
                    }
                } else {
                    $response['message'] = __('The username selected cannot be deleted Is protected.');
                }
            } else {
                $response['message'] = __('The username not found.');
            }
        } else {
            $response['message'] = __('Your not allowed to access here.');
        }

        $this->set([
            'response' => $response,
            '_serialize' => 'response',
        ]);
        $this->RequestHandler->renderAs($this, 'json');
    }

}
