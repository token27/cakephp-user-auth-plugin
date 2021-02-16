<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Network\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

class PermissionsController extends AppController {

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

        $permission = [];
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
                    $permission_data = [
                        'name' => $name,
                    ];
                    $permission_database_saved = $this->Permissions->addPermission($permission_data);
                    if ($permission_database_saved['status'] == 1) {
                        $response = [
                            'status' => 1,
                            'message' => __('Ok.'),
                            'permission_id' => $permission_database_saved['permission']['id'],
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

            if (!empty($permission)) {
                $response = [
                    'status' => 1,
                    'message' => __('Ok.'),
                    'permission' => $permission
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

            $permission_database = $this->Permissions->getPermission($name);
            if ($permission_database) {
                try {

                    $permission_database_deleted = $this->Permissions->deleteId($permission_database['id']);
                    if ($permission_database_deleted) {
                        $response = [
                            'status' => 1,
                            'message' => __('Ok.'),
                            'permission_id' => $permission_database['id'],
                        ];
                    } else {
                        $response['message'] = __('Error while try to remove the permission.');
                        $response['permission_id'] = $permission_database['id'];
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
                $response['message'] = __('The permission not found.');
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
