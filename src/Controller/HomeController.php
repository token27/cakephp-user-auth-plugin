<?php

declare(strict_types=1);

namespace UserAuth\Controller;

use Cake\Network\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

# PLUGIN
use UserAuth\Controller\UserAuthController;

class HomeController extends UserAuthController {

    public function initialize(): void {
        parent::initialize();
        $this->Auth->allow(['index', 'notallowed']);
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
     * Not Allowed method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function notallowed() {

        $this->response->withType('application/json');

        $response = [
            'status' => 0,
            'message' => __('Your not allowed to access here.'),
        ];

        $this->set([
            'response' => $response,
            '_serialize' => 'response',
        ]);
        $this->RequestHandler->renderAs($this, 'json');
    }

    /**
     * Welcome method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function welcome() {

        $this->response->withType('application/json');

        $response = [
            'status' => 1,
            'message' => __('Your welcome.'),
        ];

        $this->set([
            'response' => $response,
            '_serialize' => 'response',
        ]);
        $this->RequestHandler->renderAs($this, 'json');
    }

}
