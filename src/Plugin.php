<?php

namespace UserAuth;

use Cake\Core\BasePlugin;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Core\PluginApplicationInterface;
use Cake\Core\Configure;
use Cake\Http\Middleware;
use Cake\Http\MiddlewareQueue;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Middleware\CsrfProtectionMiddleware;

# PLUGIN
use UserAuth\Utility\Config;
use UserAuth\Listener\UserAuthEventsListener;
use Cake\Http\Middleware\SecurityHeadersMiddleware;
#
use UserAuth\Routing\Middleware\CorsMiddleware;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Plugin for Queue
 */
class Plugin extends BasePlugin {
    /**
     * @var bool
     */
//    protected $middlewareEnabled = false;

    /**
     * Load all the plugin configuration and bootstrap logic.
     *
     * The host application is provided as an argument. This allows you to load
     * additional plugin dependencies, or attach events.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void {


        /**
         * @note Optionally load additional queued config defaults from local app config
         */
        Config::loadPluginConfiguration();

        EventManager::instance()->on(new UserAuthEventsListener());
    }

    /**
     * Add routes for the plugin.
     *
     * If your plugin has many routes and you would like to isolate them into a separate file,
     * you can create `$plugin/config/routes.php` and delete this method.
     *
     * @param \Cake\Routing\RouteBuilder $routes The route builder to update.
     * @return void
     */
    public function routes(RouteBuilder $routes): void {
        /**
         * Plugin Endpoint
         * 
         * [DOMAIN.TDL]/user-auth/
         */
        $routes->plugin(
                'UserAuth',
                ['path' => '/user-auth'],
                function (RouteBuilder $builder) {

            $builder->connect('/', ['controller' => 'Home', 'action' => 'index']);
            $builder->connect('/notallowed/*', ['controller' => 'Home', 'action' => 'notallowed']);
            $builder->connect('/welcome/*', ['controller' => 'Home', 'action' => 'welcome']);
            $builder->connect('/users/:action/', ['controller' => 'Users']);
            $builder->connect('/roles/:action/', ['controller' => 'Roles']);
            $builder->connect('/permissions/:action/', ['controller' => 'Permissions']);


            $builder->fallbacks();
        }
        );
        parent::routes($routes);
    }

    /**
     * Add middleware for the plugin.
     *
     * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to update.
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue {

//        try {
//            $middlewareQueue->insertBefore(CsrfProtectionMiddleware::class, new CorsMiddleware());
//        } catch (\LogicException $exception) {
//            $middlewareQueue->add(new CorsMiddleware());
//        }

        return $middlewareQueue;
    }

}
