<?php

namespace UserAuth\Factories;

use Cake\Network\Request;

/**
 * Class IdentityActionFactory
 * @package UserAuth\Factories
 */
class IdentityActionFactory {

    /**
     * Method getAction
     * Generating action string for identity plugin
     *
     * @param Request $request
     * @return string
     */
    public static function getAction(Request $request) {
        $actionString = '';

        // check if prefix is available
        if ($request->getParam('prefix')) {
            $actionString = $actionString . $request->getParam('prefix') . ":";
        }

        // check if plugin is available
        if ($request->getParam('plugin')) {
            $actionString = $actionString . $request->getParam('plugin') . ":";
        }

        // action and controller is defined every time
        $actionString = $actionString . $request->getParam('controller') . ":";
        $actionString = $actionString . $request->getParam('action');

        return strtolower($actionString);
    }

}
