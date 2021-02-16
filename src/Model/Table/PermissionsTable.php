<?php

declare(strict_types=1);

namespace UserAuth\Model\Table;

# CAKEPHP

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotImplementedException;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Model\Table\EntityInterface;
use Cake\Utility\Inflector;
use Cake\Utility\Text;

# PLUGIN
use UserAuth\Model\Entity\Permission;
use UserAuth\Utility\Config;

#  OTHERS
use ArrayObject;
use InvalidArgumentException;

/**
 * Permissions Model
 *
 * @property \UserAuth\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 *
 * @method \App\Model\Entity\Permission newEmptyEntity()
 * @method \App\Model\Entity\Permission newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Permission[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Permission get($primaryKey, $options = [])
 * @method \App\Model\Entity\Permission findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Permission patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Permission[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Permission|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Permission saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Permission[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Permission[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Permission[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Permission[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class PermissionsTable extends Table {

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

        $this->setTable('permissions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', [
            'Roles' => ['permission_count'],
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
                ->scalar('plugin')
                ->maxLength('plugin', 255)
                ->allowEmptyString('plugin');

        $validator
                ->scalar('controller')
                ->maxLength('controller', 255)
                ->allowEmptyString('controller');

        $validator
                ->scalar('action')
                ->maxLength('action', 255)
                ->allowEmptyString('action');

        $validator
                ->scalar('params')
                ->allowEmptyString('params');

        $validator
                ->boolean('allowed')
                ->allowEmptyString('allowed');

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
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);

        return $rules;
    }

    /**
     * Cleanning the rules cache on every save see class RoleAuthorize for cache using
     * @return type 
     */
    public function afterSave(Event $event, Permission $entity, \ArrayObject $options) {
        $roleId = null;

        if (isset($entity->role_id) && $entity->role_id && $entity->role_id != '') {
            $roleId = $entity->role_id;
        }
        if ($roleId) {
            try {
                Cache::delete(Permission::cacheKeyPrefix . $roleId);
            } catch (\Exception $e) {
                
            }
        }
    }

    /**
     * Cleanning the rules cache on every save see class RoleAuthorize for cache using
     * @return type 
     */
    public function beforeDelete(Event $event, Permission $entity, \ArrayObject $options) {
        $roleId = null;

        if (isset($entity->role_id) && $entity->role_id && $entity->role_id != '') {
            $roleId = $entity->role_id;
        }
        if ($roleId) {
            try {
                Cache::delete(Permission::cacheKeyPrefix . $roleId);
            } catch (\Exception $e) {
                
            }
        }
    }

    /**
     * Get all the permissions 
     * by role_id     
     *           
     * @param String $role_id The Role ID to get the permissions.
     * @return Mixed false|array Return an array list with the permissions.
     */
    public function getPermissionsForRoleId($role_id) {


        $permissions = false;

        try {
            $query = $this->query();

            $permissions = $query->find('all')
                    ->where(['role_id' => $role_id])
                    ->all()
                    ->toArray();
        } catch (\Exception $e) {
            $permissions = false;
        }


        return $permissions;
    }

    /**
     * Add permission
     * 
     * @param array $data
     * @return \UserAuth\Model\Entity\Permission 
     */
    public function addPermission(array $data = []) {

        $data += [
            'role_id' => !empty($data['role_id']) && $data['role_id'] !== "" ? $data['role_id'] : null,
            'plugin' => !empty($data['plugin']) && $data['plugin'] !== "" ? Inflector::camelize($data['plugin']) : null,
            'controller' => !empty($data['controller']) && $data['controller'] !== "" ? Inflector::camelize($data['controller']) : null,
            'action' => !empty($data['action']) && $data['action'] !== "" ? $data['action'] : null,
            'params' => !empty($data['params']) && $data['params'] !== "" ? $data['params'] : null,
            'allowed' => !empty($data['allowed']) && $data['allowed'] !== "" ? intval($data['allowed']) : 1,
        ];

        $permission = $this->newEntity($data);

        return $this->saveOrFail($permission);
    }

    /**
     * Get permissions
     *    
     */
    public function getPermissions(array $options = [], int $limit = 0, int $page = 1) {


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
