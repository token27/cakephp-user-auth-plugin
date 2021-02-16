# CakePHP Users Token Authentication and Roles Authorize (ACL) Plugin 


## Installation
```
composer require token27/cakephp-user-auth-plugin
```

### Load the plugin
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

### Create database plugin schema
Run the following command in the CakePHP console to create the tables using the Migrations plugin:
```sh
bin/cake migrations migrate -p UserAuth
```

### Create users

Superadmin user
```sh
bin/cake UserAuth superadmin -p <password>
```

Other user
```sh
bin/cake UserAuth add -u <username> -p <password> -r <role_id/name>
```

## Configuration

### Edit your `you-app-path/src/Controller/AppController.php` 

```
use Throwable;
use Exception;
use UserAuth\Utility\Config;
use Cake\Event\EventInterface;

class AppController extends Controller {

    public function initialize(): void {
        parent::initialize();

        ....
        // Load other components here

        $this->loadComponent('Auth', [
            'loginAction' => [
                'plugin' => 'UserAuth',
                'controller' => 'Home',
                'action' => 'notallowed'
            ],
            'loginRedirect' => [
                'plugin' => 'UserAuth',
                'controller' => 'Home',
                'action' => 'welcome'
            ],
            'logoutRedirect' => [
                'plugin' => 'UserAuth',
                'controller' => 'Home',
                'action' => 'index'
            ],
            'authorize' => [
                'UserAuth.Roles' => [
                    'debug' => true,
                    'authorizeAll' => false,
                ]
            ],
            'authenticate' => [
                'Form' => [
                    'userModel' => Config::defaultUserModel(),
                    'scope' => [
                        Config::defaultUserModel() . '.status' => 1
                    ]
                ],
                'UserAuth.Token' => [
                    'userModel' => Config::defaultUserModel(),
                    'parameter' => 'token',
                    'scope' => [
                        Config::defaultUserModel() . '.status' => 1
                    ],
                    'fields' => [
                        'username' => 'id'
                    ],
                    'queryDatasource' => true,
                    'unauthenticatedException' => null 
                ]
            ],
            'unauthorizedRedirect' => true,
            'checkAuthIn' => 'Controller.initialize'
        ]);
    }

    /**
     * 
     * @param \Cake\Event\EventInterface $event
     */
    public function beforeFilter(EventInterface $event) {
        parent::beforeFilter($event);
        $this->Auth->startup($event);
    }
```

### Setup ACL

Allow action in controller
```
class ExampleController extends AppController {

    public function initialize(): void {
        parent::initialize();
        $this->Auth->allow(['display']);
    }

    public function display() {
        // This functions is allowed without ALC verification
    }
}
```

Add role from command CLI:

```sh
bin/cake UserAuth role add <role-name>
```

Add permission from command CLI:

```sh
bin/cake UserAuth permission add --plugin <plugin-name> --controller <controller-name> --action <action-name> --allowed <status>
```

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


# Responses

## Login
```
{
    "status": 1,
    "message": "Ok.",
    "success": "Login Successfully.",
    "login": {
        "id": "fcd7afe0-9ecd-40f1-ba7a-b3fc18bc3e76",
        "username": "superadmin",
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsImlkIjoiZmNkN2FmZTAtOWVjZC00MGYxLWJhN2EtYjNmYzE4YmMzZTc2IiwidXNlcm5hbWUiOiJzdXBlcmFkbWluIiwic3ViIjoiZmNkN2FmZTAtOWVjZC00MGYxLWJhN2EtYjNmYzE4YmMzZTc2IiwiaWF0IjoxNjEzNDY2MTczLCJleHAiOjE2MTM1NTI1NzN9.6_IuOtuQnADrXxSIRaTOOFqCvsflI36O41405GIgY3Y"
    }
}
```

## Token Verify
```
{
    "status": 1,
    "message": "Token successfully.",
    "payload": {
        "alg": "HS256",
        "id": "fcd7afe0-9ecd-40f1-ba7a-b3fc18bc3e76",
        "username": "superadmin",
        "sub": "fcd7afe0-9ecd-40f1-ba7a-b3fc18bc3e76",
        "iat": 1613466173,
        "exp": 1613552573
    }
}
```

### Ok
```
{
    "status": 1,
    "message": "Ok.",    
}
```