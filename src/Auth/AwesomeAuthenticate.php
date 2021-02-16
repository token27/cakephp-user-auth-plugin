<?php

namespace UserAuth\Auth;

use Cake\Auth\BaseAuthenticate;
#use Cake\Network\Request;
#use Cake\Network\Response;
use Cake\Http\ServerRequest;
use Cake\Http\Response;
use Cake\Event\EventListenerInterface;
use Cake\Network\Http\Client;
use Cake\Core\Configure;

class AwesomeAuthenticate extends BaseAuthenticate {

    public function authenticate(ServerRequest $request, Response $response) {

        /**
         * Components don't expose Controllers but we could get it from component registry
         * @see http://stackoverflow.com/questions/28876233/cakephp-3-0-load-model-insid...
         */
        $controller = $this->_registry->getController();

        $controller->ProprietaryAPI->request([
            'method' => 'user/authenticate',
            'data' => json_encode($request->data),
            'headers' => []
        ]);

        /**
         * Check if response returns an auth token. If found
         * user is authenticated and we can safely log in.
         */
        if ($auth_token = $controller->ProprietaryAPI->validate_token()) {
            return array_merge($request->data, array('auth_token' => $auth_token));
        }

        // User is not authenticated
        return FALSE;
    }

}
