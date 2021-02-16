<?php

declare(strict_types=1);

namespace UserAuth\Controller;

use Cake\Core\Configure;
use Queued\Queued\Config;
use UserAuth\Controller\UserAuthController;

/**
 * Dashboard Controller 
 */
class DashboardController extends UserAuthController {

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
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index() {
        
    }

}
