<?php

namespace UserAuth\Listener;

# CAKEPHP

use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;

class UserAuthEventsListener implements EventListenerInterface {

    /**
     * Returns a list of events this object is implementing.
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents(): array {
        return [
            'User.registered' => 'sendWelcome'
        ];
    }

    /**
     * Sends a welcome email to new user
     *
     * eventOptions
     *  - userId: User ID
     *
     * @param \Cake\Event\Event $event Event
     * @param array $eventOptions Event options
     * @return void
     */
    public function sendWelcome($event, $eventOptions) {
        
    }

}
