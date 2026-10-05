<?php

namespace Warehouse;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager =
            $e->getApplication()
                ->getEventManager();

        $moduleRouteListener =
            new ModuleRouteListener();

        $moduleRouteListener->attach(
            $eventManager
        );

        $eventManager->attach(
            MvcEvent::EVENT_ROUTE,
            [$this, 'checkAuthentication'],
            -100
        );
    }

    public function checkAuthentication(MvcEvent $e)
    {
        $routeMatch =
            $e->getRouteMatch();

        if (!$routeMatch) {
            return;
        }

        $routeName =
            $routeMatch->getMatchedRouteName();

        /*
         * Public routes.
         *
         * Login and logout must always be
         * accessible without authentication.
         */

        $publicRoutes = [
            'login',
            'logout'
        ];

        if (in_array($routeName, $publicRoutes, true)) {
            return;
        }

        /*
         * Check login session.
         */

        $session =
            new Container('warehouse');

        if ($session->loggedIn) {
            return;
        }

        /*
         * User is not logged in.
         *
         * Redirect to login only when the
         * current route is NOT already login.
         */

        $router =
            $e->getRouter();

        $loginUrl =
            $router->assemble(
                [],
                [
                    'name' => 'login'
                ]
            );

        $response =
            $e->getResponse();

        /*
         * Prevent redirect loop.
         */

        $currentUrl =
            $e->getRequest()
                ->getUri()
                ->toString();

        if (
            rtrim($currentUrl, '/') ===
            rtrim($loginUrl, '/')
        ) {
            return;
        }

        $response->getHeaders()
            ->addHeaderLine(
                'Location',
                $loginUrl
            );

        $response->setStatusCode(302);

        return $response;
    }

    public function getConfig()
    {
        return include __DIR__ .
            '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ =>
                        __DIR__ . '/src',
                ],
            ],
        ];
    }
}
