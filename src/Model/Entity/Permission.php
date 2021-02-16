<?php

declare(strict_types=1);

namespace UserAuth\Model\Entity;

use Cake\ORM\Entity;

/**
 * Permission Entity
 *
 * @property string $id
 * @property string|null $role_id
 * @property string|null $plugin
 * @property string|null $controller
 * @property string|null $action
 * @property string|null $params
 * @property bool|null $allowed
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Role $role
 */
class Permission extends Entity {

    const cacheKeyPrefix = 'PermissionsForUserRole_';

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'role_id' => true,
        'plugin' => true,
        'controller' => true,
        'action' => true,
        'params' => true,
        'allowed' => true,
        'created' => true,
        'modified' => true,
        'role' => true,
    ];

}
