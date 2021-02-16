<?php

declare(strict_types=1);

namespace UserAuth\Controller;

use Cake\Network\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

# PLUGIN
use UserAuth\Controller\Ajax\UserAuthAjaxController;

class RolesController extends UserAuthAjaxController {

    public function initialize(): void {
        parent::initialize();
        $this->Auth->allow(['login', 'token']);
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
        $options_roles = [];

        $roles = [];

        if ($this->isSuperadmin()) {
            try {
                $roles_database = $this->Roles->getRoles($options_roles, $limit, $page);
                if (!empty($roles_database['results'])) {
                    $response = [
                        'status' => 1,
                        'message' => __('Ok.'),
                    ];
                } else {
                    $response['message'] = __('No roles found.');
                }
                $response = array_merge($response, $roles_database);
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

        $role = [];
        $name = "";

        if ($this->request->is('post') || $this->request->is('ajax')) {
            try {
                if ($this->request->getData('name')) {
                    $name = $this->request->getData('name');
                }
            } catch (\Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => __('Something wrong, please try again later...'),
                    'error' => $e->getMessage(),
                ];
            }
        }
        if ($this->request->getQuery('name')) {
            $name = $this->request->getQuery('name');
        }

        if ($this->isSuperadmin()) {
            if ($name) {
                try {
                    $role_data = [
                        'name' => $name,
                    ];
                    $role_database_saved = $this->Roles->addRole($role_data);
                    if ($role_database_saved['status'] == 1) {
                        $response = [
                            'status' => 1,
                            'message' => __('Ok.'),
                            'role_id' => $role_database_saved['role']['id'],
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
                $response['message'] = __('The name cannot be empty.');
            }

            if (!empty($role)) {
                $response = [
                    'status' => 1,
                    'message' => __('Ok.'),
                    'role' => $role
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

        $name = "";


        if ($this->request->is('post') || $this->request->is('ajax')) {
            try {
                if ($this->request->getData('name')) {
                    $name = $this->request->getData('name');
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

        if ($this->request->getQuery('name')) {
            $name = $this->request->getQuery('name');
        }

        if ($this->isSuperadmin()) {
            $role_database = $this->Roles->getRole($name);
            if ($role_database) {
                $can_be_deleted = true;

                if (isset($role_database['name']) && ($role_database['name'] == "superadmin" || $role_database['name'] == "admin" )) {
                    $can_be_deleted = false;
                }
                if ($can_be_deleted) {
                    try {
                        $role_database_deleted = $this->Roles->deleteId($role_database['id']);
                        if ($role_database_deleted) {
                            $response = [
                                'status' => 1,
                                'message' => __('Ok.'),
                                'role_id' => $role_database['id'],
                            ];
                        } else {
                            $response['message'] = __('Error while try to remove the role.');
                            $response['role_id'] = $role_database['id'];
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
                    $response['message'] = __('The role selected cannot be deleted Is protected.');
                }
            } else {
                $response['message'] = __('The role not found.');
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
