<?php

namespace UserAuth\Factories;

use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
//use Http\Exception\RuntimeException;
use RuntimeException;

class UsersFactory {

    /**
     * Metnog getOperatorsEmails
     * Give emails of all users which have set their role to Operators
     *
     * @return \Cake\ORM\Query
     */
    public static function getOperatorsEmails() {
        $usersT = TableRegistry::getTableLocator()->get('UserAuth.Users');

        $emails = $usersT->find()->matching('Roles', function ($q) {
                    return $q->where(['Roles.name' => RoleFactory::OPERATORS_ROLE]);
                })->select(['Users.email']);

        // throw error when there is no operator
        if ($emails->count() == 0)
            throw new InternalErrorException(__('Cannot send email, please contact your administrator. Error code; ') . '36b5bc2a-b25d-4b82-830b-11fa357f6ba9');

        return $emails;
    }

}
