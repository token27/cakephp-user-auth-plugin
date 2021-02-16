<?php

declare(strict_types=1);

namespace UserAuth\Model\Table;

# CAKEPHP

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
# PLUGIN
use UserAuth\Utility\Config;

#  OTHERS
use ArrayObject;
use InvalidArgumentException;

/**
 * Roles Model
 *
 * @property \UserAuth\Model\Table\PermissionsTable&\Cake\ORM\Association\HasMany $Permissions
 * @property \UserAuth\Model\Table\UsersTable&\Cake\ORM\Association\HasMany $Users
 *
 * @method \App\Model\Entity\Role newEmptyEntity()
 * @method \App\Model\Entity\Role newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Role[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Role get($primaryKey, $options = [])
 * @method \App\Model\Entity\Role findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Role patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Role[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Role|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Role saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RolesTable extends Table {

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

        $this->setTable('roles');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Permissions', [
            'foreignKey' => 'role_id',
        ]);
        $this->hasMany('Users', [
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
                ->scalar('name')
                ->maxLength('name', 255)
                ->requirePresence('name', 'create')
                ->notEmptyString('name')
                ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
                ->integer('user_count')
                ->notEmptyString('user_count');

        $validator
                ->integer('permission_count')
                ->notEmptyString('permission_count');

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
        $rules->add($rules->isUnique(['name']), ['errorField' => 'name']);

        return $rules;
    }

    /**
     * Get all the permissions 
     * by role_id     
     *           
     * @param String $roleName The Role ID to get the permissions.
     * @return Mixed false|array Return an array list with the permissions.
     */
    public function getRoleByName($roleName) {

        $role = false;

        try {
            $query = $this->query();

            $role = $query->find('all')
                    ->where(['name' => $roleName])
                    ->first();
            if ($role) {
                $role = $role->toArray();
            }
        } catch (\Exception $e) {
            $role = false;
        }

        return $role;
    }

    /**
     * Add Role
     * 
     * @param array $data
     * @return \UserAuth\Model\Entity\Role 
     */
    public function addRole(array $data = []) {

        $data += [
            'name' => $data['name'] !== null && $data['name'] !== "" ? $data['name'] : "",
        ];

        $role = $this->newEntity($data);

        return $this->saveOrFail($role);
    }

    /**
     * Get role
     * 
     * @param string $id The role id/name from database
     * @return \UserAuth\Model\Entity\Role|null
     */
    public function getRole(string $id) {

        $conditions = [
            'OR' => [
                'id' => $id,
                'name' => $id,
            ]
        ];

        return $this->find()
                        ->where($conditions)
                        ->orderDesc('modified')
                        ->enableHydration(false)
                        ->first();
    }

    /**
     * Get roles
     *    
     */
    public function getRoles(array $options = [], int $limit = 0, int $page = 1) {


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
                    case "from":
                        $where['created >='] = $optionValue;
                        break;
                    case "to":
                        $where['created <='] = $optionValue;
                        break;
                    default:
                    case "id":
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
