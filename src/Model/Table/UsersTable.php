<?php

declare(strict_types=1);

namespace UserAuth\Model\Table;

# CAKEPHP

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

# PLUGIN
use UserAuth\Utility\Config;

#  OTHERS
use ArrayObject;
use InvalidArgumentException;

/**
 * Users Model
 *
 * @property \UserAuth\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles 
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class UsersTable extends Table {

    public const DRIVER_MYSQL = 'Mysql';
    public const DRIVER_POSTGRES = 'Postgres';
    public const DRIVER_SQLSERVER = 'Sqlserver';
    public const STATS_LIMIT = 100000;

    /**
     * set connection name
     *
     * @return string
     */
    public static function defaultConnectionName(): string {
        $connection = Config::defaultDatabaseConnection();
        if (!empty($connection)) {
            return $connection;
        }

        return parent::defaultConnectionName();
    }

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', [
            'Roles' => ['user_count'],
        ]);

        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator {
        $validator
                ->uuid('id')
                ->allowEmptyString('id', null, 'create');

        $validator
                ->scalar('username')
                ->maxLength('username', 255)
                ->requirePresence('username', 'create')
                ->notEmptyString('username')
                ->add('username', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
                ->scalar('password')
                ->maxLength('password', 255)
                ->requirePresence('password', 'create')
                ->notEmptyString('password');

        $validator
                ->email('email')
                ->allowEmptyString('email')
                ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
                ->scalar('first_name')
                ->maxLength('first_name', 50)
                ->allowEmptyString('first_name');

        $validator
                ->scalar('last_name')
                ->maxLength('last_name', 50)
                ->allowEmptyString('last_name');

        $validator
                ->scalar('thumbnail')
                ->maxLength('thumbnail', 255)
                ->allowEmptyString('thumbnail');

        $validator
                ->scalar('token')
                ->maxLength('token', 255)
                ->allowEmptyString('token');

        $validator
                ->dateTime('token_expires')
                ->allowEmptyDateTime('token_expires');

        $validator
                ->scalar('api_token')
                ->maxLength('api_token', 255)
                ->allowEmptyString('api_token');

        $validator
                ->dateTime('activation_date')
                ->allowEmptyDateTime('activation_date');

        $validator
                ->scalar('secret')
                ->maxLength('secret', 32)
                ->allowEmptyString('secret');

        $validator
                ->boolean('secret_verified')
                ->allowEmptyString('secret_verified');

        $validator
                ->dateTime('tos_date')
                ->allowEmptyDateTime('tos_date');

        $validator
                ->boolean('is_superuser')
                ->notEmptyString('is_superuser');

        $validator
                ->scalar('additional_data')
                ->allowEmptyString('additional_data');

        $validator
                ->integer('status')
                ->notEmptyString('status');


        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }

    /**
     * Add user 
     *           
     * @param String $username
     * @param String $password
     * @param String $role_id     
     * @return Mixed
     */
    public function addUsername(string $username, string $password, string $role_id) {

        $user = false;

        try {
            $user = $this->newEmptyEntity();
            $user->username = $username;
            $user->password = $password;
            $user->role_id = $role_id;
        } catch (\Exception $e) {
            $user = false;
        }

        if ($user) {
            $saved = false;
            try {
                $saved = $this->save($user);
            } catch (\Exception $e) {
                $saved = false;
            }
            if ($saved) {
                try {
                    $user = $saved->toArray();
                } catch (\Exception $e) {
                    $user = false;
                }
            }
        }

        return $user;
    }

    /**
     * Add user
     * 
     * @param array $data
     * @return \UserAuth\Model\Entity\User|null
     */
    public function addUser(array $data = []) {

        $data += [
            'role_id' => isset($data['role_id']) && !empty($data['role_id']) ? $data['role_id'] : null,
            'username' => isset($data['username']) && !empty($data['username']) ? $data['username'] : null,
            'password' => isset($data['password']) && !empty($data['password']) ? $data['password'] : null,
            'status' => isset($data['status']) && !empty($data['status']) ? intval($data['status']) : 1,
        ];

        $user = $this->newEntity($data);

        return $this->saveOrFail($user);
    }

    /**
     * Get user
     * 
     * @param string $id The user id/username from database
     * @return \UserAuth\Model\Entity\User|null
     */
    public function getUser(string $id) {

        $conditions = [
            'OR' => [
                'id' => $id,
                'username' => $id,
            ]
        ];

        return $this->find()
                        ->where($conditions)
                        ->orderDesc('modified')
                        ->enableHydration(false)
                        ->first();
    }

    /**
     * Get user
     * 
     * @param string $id The user id/username from database
     * @return \UserAuth\Model\Entity\User|null
     */
    public function getUsers(array $options = [], int $limit = 0, int $page = 1) {


        $response = [
            'page' => intval($page),
            'limit' => intval($limit),
            'count' => 0,
            'results' => []
        ];

        $conditions = [];

        if (!empty($options)) {
            foreach ($options as $key => $value) {
                switch ($key) {
                    case "status":
                        $where[$key] = intval($value);
                        break;
                    case "from":
                        $where['created >='] = $optionValue;
                        break;
                    case "to":
                        $where['created <='] = $optionValue;
                        break;
                    default:
                    case "id":
                    case "role_id":
                        if (!is_array($value)) {
                            if ($value !== null && $value !== "") {
                                if ($value === "NULL") {
                                    $conditions[] = $key . ' IS NULL'; //                         
                                } else if ($value === "!NULL") {
                                    $conditions[] = $key . ' IS NOT NULL';
                                } else {
                                    $conditions[$key] = $value;
                                }
                            }
                        } else {
                            if (!empty($value)) {
                                $conditions[$key . ' IN'] = $value;
                            }
                        }
                        break;
                }
            }
        }

        $query = $this->find()
                ->where($conditions)
                ->orderDesc('modified')
                ->enableHydration(false);

        if ($limit >= 1) {
            $query->limit($limit);
        }
        if ($page > 1) {
            $query->page($limit);
        }

        $query_results = $query->all()->toArray();

        $count_total = intval($query->count());

        $results = isset($query_results['items']) ? $query_results['items'] : [];

        $count = intval($query->count());
        $response = [
            'page' => intval($page),
            'limit' => intval($limit),
            'count' => $count_total,
            'results' => $results
        ];

        return $response;
    }

    public function deleteId(string $userId) {
        $deleted = false;
        $entity = null;
        try {
            $entity = $this->get($userId);
        } catch (Throwable $t) {
            $entity = null;
        } catch (Exception $ex) {
            $entity = null;
        }
        if ($entity) {
            try {
                $deleted = $this->delete($entity);
            } catch (Throwable $t) {
                $deleted = false;
            } catch (Exception $ex) {
                $deleted = false;
            }
        }
        return $deleted;
    }

}
