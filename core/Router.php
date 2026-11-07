<?php
namespace Core;

/**
 * Class Router
 * Clean URL and Query dispatcher mapping routes to Controller actions
 */
class Router
{
    private array $routes = [
        'login'        => ['controller' => 'Controllers\AuthController', 'action' => 'login'],
        'logout'       => ['controller' => 'Controllers\AuthController', 'action' => 'logout'],
        'dashboard'    => ['controller' => 'Controllers\DashboardController', 'action' => 'index'],
        'vehicles'     => ['controller' => 'Controllers\VehicleController', 'action' => 'index'],
        'policies'     => ['controller' => 'Controllers\PolicyController', 'action' => 'index'],
        'claims'       => ['controller' => 'Controllers\ClaimController', 'action' => 'index'],
        'survey'       => ['controller' => 'Controllers\SurveyController', 'action' => 'index'],
        'audit'        => ['controller' => 'Controllers\AuditLogController', 'action' => 'index'],
        'settings'     => ['controller' => 'Controllers\SettingsController', 'action' => 'index'],
        'profile'      => ['controller' => 'Controllers\SettingsController', 'action' => 'index'],
        'notifications'=> ['controller' => 'Controllers\NotificationController', 'action' => 'index'],
    ];

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $page = $_GET['page'] ?? 'dashboard';
        $action = $_GET['action'] ?? null;

        if (!is_string($page) || !isset($this->routes[$page])) {
            http_response_code(404);
            $controller = new \Controllers\DashboardController();
            $controller->notFound();
            return;
        }

        $route = $this->routes[$page];
        $controllerClass = $route['controller'];
        $controller = new $controllerClass();

        $method = (is_string($action) && $action !== '') ? $action : $route['action'];

        // Only public, non-magic controller methods are routable; anything else falls back to index
        if (str_starts_with($method, '__') || !is_callable([$controller, $method])) {
            $method = 'index';
        }

        $controller->$method();
    }
}
