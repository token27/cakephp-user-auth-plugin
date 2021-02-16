<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Initial extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up()
    {
        $this->table('permissions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('role_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('plugin', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('controller', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('action', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('params', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('allowed', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'role_id',
                ]
            )
            ->create();

        $this->table('roles', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('permission_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'name',
                ],
                ['unique' => true]
            )
            ->create();

        $this->table('users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('role_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('username', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('first_name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('last_name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('thumbnail', 'string', [
                'default' => '/img/no-image.png',
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('token', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('token_expires', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('api_token', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('activation_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('secret', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('secret_verified', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('tos_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('is_superuser', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('additional_data', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('status', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'username',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'email',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'role_id',
                ]
            )
            ->addIndex(
                [
                    'is_superuser',
                ]
            )
            ->addIndex(
                [
                    'status',
                ]
            )
            ->addIndex(
                [
                    'token',
                ]
            )
            ->create();

        $this->table('permissions')
            ->addForeignKey(
                'role_id',
                'roles',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'CASCADE',
                ]
            )
            ->update();

        $this->table('users')
            ->addForeignKey(
                'role_id',
                'roles',
                'id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'SET_NULL',
                ]
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down()
    {
        $this->table('permissions')
            ->dropForeignKey(
                'role_id'
            )->save();

        $this->table('users')
            ->dropForeignKey(
                'role_id'
            )->save();

        $this->table('permissions')->drop()->save();
        $this->table('roles')->drop()->save();
        $this->table('users')->drop()->save();
    }
}
