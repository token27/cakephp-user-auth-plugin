<?php

declare(strict_types=1);

use Migrations\AbstractSeed;
use Cake\Utility\Text;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Cake\Auth\DefaultPasswordHasher;

# PLUGIN
use UserAuth\Factories\RoleFactory;

/**
 * Groups seed.
 */
class InitialSeed extends AbstractSeed {

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     *
     * @return void
     */
    public function run() {

        $superadminRoleUUID = Text::uuid();

        $data = [
            [
                'name' => RoleFactory::ADMINISTRATORS_ROLE,
                'uuid' => $superadminRoleUUID
            ],
            [
                'name' => RoleFactory::OPERATORS_ROLE,
                'uuid' => Text::uuid()
            ],
            [
                'name' => RoleFactory::WEBMASTER_ROLE,
                'uuid' => Text::uuid()
            ],
            [
                'name' => RoleFactory::USERS_ROLE,
                'uuid' => Text::uuid()
            ]
        ];

        $table = $this->table('UserAuth.Roles');
        $table->truncate();
        $table->insert($data)->save();

        $roles = TableRegistry::getTableLocator()->get('UserAuth.Roles');
        $roleID = $roles->find()->where(['uuid' => $superadminRoleUUID])->select(['id'])->first();

        $userData = [
            'email' => 'superadmin@localhost.com',
            'username' => 'superadmin',
            'password' => (new DefaultPasswordHasher)->hash('1234'),
            'role_id' => $roleID->id,
            'created' => \Cake\Chronos\Date::now()->toFormattedDateString(),
            'modified' => \Cake\I18n\Date::now()->toFormattedDateString()
        ];

        $userTable = $this->table('UserAuth.Users');
        $userTable->truncate();
        $userTable->insert($userData)->save();
    }

}
