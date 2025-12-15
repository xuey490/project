<?php
// config/routes.php
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;



$routes = new RouteCollection();

// 手动路由（优先级高）- 修正后 自定义参数注入方式
$routes->add('admin_home', new Route(
    '/index', // 路由路径
    [
        '_controller' => 'App\Controllers\Home::index' // 控制器默认参数
    ],
    [
        // 仅放URL参数的正则约束（示例：若路径是/index/{id}，则加'id' => '\d+'）
    ],
    [
        // 路由选项：存放自定义参数（中间件、自定义标识等）
        'params' => ['_middleware' => '\App\Middlewares\AuthMiddleware']
    ],
    '', // 主机名（如限定admin.example.com）
    [], // schemes（如['https']强制HTTPS）
    ['GET'] // 允许的请求方法
));

// 示例：带参数的手动路由（如 /api/user/123）
$routes->add('api_user', new Route(
    '/apis/user/{id}', // 带参数的路径
    [
        '_controller' => 'App\Controllers\Api\User::show',
        'id' => 1 // 参数默认值（可选）
    ],
    [
        'id' => '\d+' // 参数约束：id必须是数字（可选，增强路由安全性）
    ],
    [],
    '',
    [],
    ['GET']
));



$routes->add('admin.dashboard', new Route(
    '/admin/dashboard',
    ['_controller' => 'App\Controllers\Admin\Dashboard::index'],
    [],
    ['_middleware' => ['App\Middleware\AdminAuthMiddleware']]
));



//测试熔断器
$routes->add('test_circuit', new Route('/test/circuit', [
    '_controller' => 'App\Controllers\Test::circuitAction'
]));

$routes->add('test_healthy', new Route('/test/healthy', [
    '_controller' => 'App\Controllers\Test::healthyAction'
]));




return $routes;