<?php

declare(strict_types=1);

namespace UserAuth\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * User Entity
 *
 * @property string $id
 * @property string|null $role_id
 * @property string $username
 * @property string $password
 * @property string|null $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $thumbnail
 * @property string|null $token
 * @property \Cake\I18n\FrozenTime|null $token_expires
 * @property string|null $api_token
 * @property \Cake\I18n\FrozenTime|null $activation_date
 * @property string|null $secret
 * @property bool|null $secret_verified
 * @property \Cake\I18n\FrozenTime|null $tos_date
 * @property bool $is_superuser
 * @property string|null $additional_data
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property int $status 
 *
 * @property \App\Model\Entity\Role $role 
 */
class User extends Entity {

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
        'username' => true,
        'password' => true,
        'email' => true,
        'first_name' => true,
        'last_name' => true,
        'thumbnail' => true,
        'token' => true,
        'token_expires' => true,
        'api_token' => true,
        'activation_date' => true,
        'secret' => true,
        'secret_verified' => true,
        'tos_date' => true,
        'is_superuser' => true,
        'additional_data' => true,
        'created' => true,
        'modified' => true,
        'status' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password',
        'token',
    ];

    protected function _setPassword($password) {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher)->hash($password);
        }
    }

}
