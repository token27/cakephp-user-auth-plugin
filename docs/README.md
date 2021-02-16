# CakePHP Users Authentication Plugin Documentation


## Installation
```
composer require token27/cakephp-user-auth-plugin
```

## Load the plugin
If you prefer load the plugin from command CLI:
```sh
bin/cake plugin load UserAuth
```

If you want edit in your `you-app-path/src/Application.php`'s bootstrap():
```php
$this->addPlugin('UserAuth');
```

If you want to also access the backend controller (not just using CLI), you need to use
```php
$this->addPlugin('UserAuth', ['routes' => true]);
```

## Create database plugin schema
Run the following command in the CakePHP console to create the tables using the Migrations plugin:
```sh
bin/cake migrations migrate -p UserAuth
```

## Configuration

### App configuration
Disable/Enable Cross Site Request Forgery (CSRF) Protection Middleware.
You must modify the file called `you-app-path/src/Application.php`.
Comment this lines:
```
->add(new CsrfProtectionMiddleware([
    'httponly' => true,
]))
    
```

### Global configuration
The plugin allows some simple runtime configuration.
You may create a file called `app_users_authentication.php` inside your `config` folder (NOT the plugins config folder) to set the following values:

- Use a different connection:

```php
$config['UserAuth']['connection'] = 'custom'; // Defaults to 'default'
```


# Endpoints

## Users
| METHOD | PATH | PARAMS | Description |
| --- | ------ | -------- | --- |
| **POST** | **/user-auth/users/login** | username, password | User login. |
| **POST** | **/user-auth/users/token** | token | Verify token. |
| **GET/POST** | **/user-auth/users/logout** | - | User logout. |
| **GET/POST** | **/user-auth/users/list** | role_id, status | Get users list by params. (Only for superadmin role) |
| **GET/POST** | **/user-auth/users/add** | role_id, username, password, status | Add user to the database. (Only for superadmin role) |
| **GET/POST** | **/user-auth/users/remove** | user_id | Delete user from the database. (Only for superadmin role) |

## Roles
| METHOD | PATH | PARAMS | Description |
| --- | ------ | -------- | --- |
| **GET/POST** | **/user-auth/roles/list** | - | Get roles list. (Only for superadmin role) |
| **GET/POST** | **/user-auth/roles/add** | name | Add role to the database. (Only for superadmin role) |
| **GET/POST** | **/user-auth/roles/remove** | role_id | Delete role from the database. (Only for superadmin role) |

## Permissions
| METHOD | PATH | PARAMS | Description |
| --- | ------ | -------- | --- |
| **GET/POST** | **/user-auth/permissions/list** | - | Get permissions list. (Only for superadmin role) |
| **GET/POST** | **/user-auth/permissions/add** | - | Add permission to the database. (Only for superadmin role) |
| **GET/POST** | **/user-auth/permissions/remove** | role_id | Delete permission from the database. (Only for superadmin role) |

## Postman
You can test endpoints with a Postman.
File: `you-app-path/src/vendor/token27/cakephp-user-auth-plugin/docs/postman.json`
(You must modify the enviroment vars from postman domain, username, etc..)
