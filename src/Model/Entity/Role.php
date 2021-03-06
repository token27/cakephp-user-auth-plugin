<?php

declare(strict_types=1);

namespace UserAuth\Model\Entity;

use Cake\ORM\Entity;

/**
 * Role Entity
 *
 * @property string $id
 * @property string $name
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $user_count
 * @property int $permission_count
 *
 * @property \App\Model\Entity\Permission[] $permissions
 * @property \App\Model\Entity\User[] $users
 */
class Role extends Entity {

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
        'name' => true,
        'created' => true,
        'modified' => true,
        'user_count' => true,
        'permission_count' => true,
        'permissions' => true,
        'users' => true,
    ];

}
