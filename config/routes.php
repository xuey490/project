<?php
// config/routes.php
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

// =========================================================================
// System Routes (Prefix: /system)
// =========================================================================

// Auth & Core
$routes->add('auth_login', new Route(
    '/system/login',
    ['_controller' => 'App\Controllers\Admin\Auth::login'],
    [], [], '', [], ['POST']
));
$routes->add('auth_logout', new Route(
    '/system/logout',
    ['_controller' => 'App\Controllers\Admin\Auth::logout'],
    [], [], '', [], ['POST']
));

// Auth Module Endpoints (matching src/api/system/auth/auth.ts)
$authRoutes = [
    'auth_user_info' => ['/system/auth/user-info', 'GET', 'getInfo'],
    'auth_user_menus' => ['/system/auth/user-menus', 'GET', 'getRouters'],
    'auth_perm_code' => ['/system/auth/perm-code', 'GET', 'getPermCode'],
    'auth_role_menu_ids' => ['/system/auth/role-menu-ids', 'GET', 'roleMenuIds'], // Mapped to Auth or Role controller? Let's put in Role but route here.
    'auth_role_scope_ids' => ['/system/auth/role-scope-ids', 'GET', 'roleScopeIds'],
    'auth_user_list_role' => ['/system/auth/user-list-by-role-id', 'GET', 'allocatedUserList'],
    'auth_user_list_exclude' => ['/system/auth/user-list-exclude-role-id', 'GET', 'unallocatedUserList'],
    'auth_remove_user_role' => ['/system/auth/remove-user-role', 'POST', 'cancelAuthUser'],
    'auth_save_user_role' => ['/system/auth/save-user-role', 'POST', 'authUser'],
    'auth_user_permissions' => ['/system/auth/user-permissions', 'GET', 'getUserPermissions'],
];

foreach ($authRoutes as $name => $route) {
    // Determine controller based on logic
    $controller = 'App\Controllers\Admin\Auth';
    if (in_array($route[2], ['roleMenuIds', 'roleScopeIds', 'allocatedUserList', 'unallocatedUserList', 'cancelAuthUser', 'authUser'])) {
        $controller = 'App\Controllers\Admin\SysRole';
    }
    
    $routes->add($name, new Route($route[0], 
        ['_controller' => $controller . '::' . $route[2]], 
        [], 
        ['_middleware' => ['App\Middlewares\AuthMiddleware']], 
        '', [], [$route[1]]
    ));
}

// System - User
// Base URL: /system/user
$userRoutes = [
    'user_list' => ['/system/user', 'GET', 'index'],
    'user_get' => ['/system/user/{id}', 'GET', 'show', ['id' => '\d+']],
    'user_add' => ['/system/user', 'POST', 'store'],
    'user_update' => ['/system/user/{id}', 'PUT', 'update', ['id' => '\d+']],
    'user_update_no_id' => ['/system/user', 'PUT', 'update'], // For cases where ID is in body
    'user_delete' => ['/system/user/{id}', 'DELETE', 'destroy', ['id' => '\d+']],
    'user_batch_delete' => ['/system/user', 'DELETE', 'batchDestroy'],
    'user_status' => ['/system/user/{id}/change-status', 'PUT', 'changeStatus', ['id' => '\d+']],
    'user_reset_pwd' => ['/system/user/reset-password', 'PUT', 'resetPassword'],
    'user_grant_role' => ['/system/user/grant-role', 'PUT', 'grantRole'],
];
foreach ($userRoutes as $name => $route) {
    $routes->add($name, new Route($route[0], 
        ['_controller' => 'App\Controllers\Admin\SysUser::' . $route[2]], 
        $route[3] ?? [], 
        ['_middleware' => ['App\Middlewares\AuthMiddleware']], 
        '', [], [$route[1]]
    ));
}

// System - Role
// Base URL: /system/role
$roleRoutes = [
    'role_list' => ['/system/role', 'GET', 'index'],
    'role_get' => ['/system/role/{id}', 'GET', 'show', ['id' => '\d+']],
    'role_add' => ['/system/role', 'POST', 'store'],
    'role_update' => ['/system/role/{id}', 'PUT', 'update', ['id' => '\d+']],
    'role_update_no_id' => ['/system/role', 'PUT', 'update'],
    'role_delete' => ['/system/role/{id}', 'DELETE', 'destroy', ['id' => '\d+']],
    'role_batch_delete' => ['/system/role', 'DELETE', 'batchDestroy'],
    'role_status' => ['/system/role/{id}/change-status', 'PUT', 'changeStatus', ['id' => '\d+']],
    'role_data_scope' => ['/system/role/data-scope', 'PUT', 'dataScope'],
];
foreach ($roleRoutes as $name => $route) {
    $routes->add($name, new Route($route[0], 
        ['_controller' => 'App\Controllers\Admin\SysRole::' . $route[2]], 
        $route[3] ?? [], 
        ['_middleware' => ['App\Middlewares\AuthMiddleware']], 
        '', [], [$route[1]]
    ));
}

// System - Menu
// Base URL: /system/menu
$menuRoutes = [
    'menu_list' => ['/system/menu', 'GET', 'index'],
    'menu_get' => ['/system/menu/{id}', 'GET', 'show', ['id' => '\d+']],
    'menu_add' => ['/system/menu', 'POST', 'store'],
    'menu_update' => ['/system/menu/{id}', 'PUT', 'update', ['id' => '\d+']],
    'menu_update_no_id' => ['/system/menu', 'PUT', 'update'],
    'menu_delete' => ['/system/menu/{id}', 'DELETE', 'destroy', ['id' => '\d+']],
    'menu_batch_delete' => ['/system/menu', 'DELETE', 'batchDestroy'],
    'menu_batch_store' => ['/system/menu/batch-store', 'POST', 'batchStore'],
];
foreach ($menuRoutes as $name => $route) {
    $routes->add($name, new Route($route[0], 
        ['_controller' => 'App\Controllers\Admin\SysMenu::' . $route[2]], 
        $route[3] ?? [], 
        ['_middleware' => ['App\Middlewares\AuthMiddleware']], 
        '', [], [$route[1]]
    ));
}

// System - Dept
// Base URL: /system/dept
$deptRoutes = [
    'dept_list' => ['/system/dept', 'GET', 'index'],
    'dept_get' => ['/system/dept/{id}', 'GET', 'show', ['id' => '\d+']],
    'dept_add' => ['/system/dept', 'POST', 'store'],
    'dept_update' => ['/system/dept/{id}', 'PUT', 'update', ['id' => '\d+']],
    'dept_update_no_id' => ['/system/dept', 'PUT', 'update'],
    'dept_delete' => ['/system/dept/{id}', 'DELETE', 'destroy', ['id' => '\d+']],
    'dept_batch_delete' => ['/system/dept', 'DELETE', 'batchDestroy'],
];
foreach ($deptRoutes as $name => $route) {
    $routes->add($name, new Route($route[0], 
        ['_controller' => 'App\Controllers\Admin\SysDept::' . $route[2]], 
        $route[3] ?? [], 
        ['_middleware' => ['App\Middlewares\AuthMiddleware']], 
        '', [], [$route[1]]
    ));
}

// System - Post
// Base URL: /system/post
$postRoutes = [
    'post_list' => ['/system/post', 'GET', 'index'],
    'post_get' => ['/system/post/{id}', 'GET', 'show', ['id' => '\d+']],
    'post_add' => ['/system/post', 'POST', 'store'],
    'post_update' => ['/system/post/{id}', 'PUT', 'update', ['id' => '\d+']],
    'post_update_no_id' => ['/system/post', 'PUT', 'update'],
    'post_delete' => ['/system/post/{id}', 'DELETE', 'destroy', ['id' => '\d+']],
    'post_batch_delete' => ['/system/post', 'DELETE', 'batchDestroy'],
    'post_status' => ['/system/post/{id}/change-status', 'PUT', 'changeStatus', ['id' => '\d+']],
];
foreach ($postRoutes as $name => $route) {
    $routes->add($name, new Route($route[0], 
        ['_controller' => 'App\Controllers\Admin\SysPost::' . $route[2]], 
        $route[3] ?? [], 
        ['_middleware' => ['App\Middlewares\AuthMiddleware']], 
        '', [], [$route[1]]
    ));
}

// System - Dict
// Base URL: /system/dict
// Type
$dictTypeRoutes = [
    'dict_type_list' => ['/system/dict/type', 'GET', 'listType'],
    'dict_type_get' => ['/system/dict/type/{id}', 'GET', 'getType', ['id' => '\d+']],
    'dict_type_add' => ['/system/dict/type', 'POST', 'addType'],
    'dict_type_update' => ['/system/dict/type/{id}', 'PUT', 'editType', ['id' => '\d+']],
    'dict_type_update_no_id' => ['/system/dict/type', 'PUT', 'editType'],
    'dict_type_delete' => ['/system/dict/type/{id}', 'DELETE', 'deleteType', ['id' => '\d+']],
    'dict_type_batch_delete' => ['/system/dict/type', 'DELETE', 'batchDeleteType'],
    'dict_type_option' => ['/system/dict/type/optionselect', 'GET', 'optionSelect'],
];
foreach ($dictTypeRoutes as $name => $route) {
    $routes->add($name, new Route($route[0], 
        ['_controller' => 'App\Controllers\Admin\SysDict::' . $route[2]], 
        $route[3] ?? [], 
        ['_middleware' => ['App\Middlewares\AuthMiddleware']], 
        '', [], [$route[1]]
    ));
}
// Data
$dictDataRoutes = [
    'dict_data_list' => ['/system/dict/data', 'GET', 'listData'],
    'dict_data_get' => ['/system/dict/data/{id}', 'GET', 'getData', ['id' => '\d+']],
    'dict_data_add' => ['/system/dict/data', 'POST', 'addData'],
    'dict_data_update' => ['/system/dict/data/{id}', 'PUT', 'editData', ['id' => '\d+']],
    'dict_data_update_no_id' => ['/system/dict/data', 'PUT', 'editData'],
    'dict_data_delete' => ['/system/dict/data/{id}', 'DELETE', 'deleteData', ['id' => '\d+']],
    'dict_data_batch_delete' => ['/system/dict/data', 'DELETE', 'batchDeleteData'],
    'dict_data_type' => ['/system/dict/data/type/{dictType}', 'GET', 'getDicts'],
];
foreach ($dictDataRoutes as $name => $route) {
    $routes->add($name, new Route($route[0], 
        ['_controller' => 'App\Controllers\Admin\SysDict::' . $route[2]], 
        $route[3] ?? [], 
        ['_middleware' => ['App\Middlewares\AuthMiddleware']], 
        '', [], [$route[1]]
    ));
}

// System - Config
// Base URL: /system/config
$configRoutes = [
    'config_list' => ['/system/config', 'GET', 'index'],
    'config_get' => ['/system/config/{id}', 'GET', 'show', ['id' => '\d+']],
    'config_add' => ['/system/config', 'POST', 'store'],
    'config_update' => ['/system/config/{id}', 'PUT', 'update', ['id' => '\d+']],
    'config_update_no_id' => ['/system/config', 'PUT', 'update'],
    'config_delete' => ['/system/config/{id}', 'DELETE', 'destroy', ['id' => '\d+']],
    'config_batch_delete' => ['/system/config', 'DELETE', 'batchDestroy'],
    'config_key' => ['/system/config/configKey/{configKey}', 'GET', 'getConfigKey'],
];
foreach ($configRoutes as $name => $route) {
    $routes->add($name, new Route($route[0], 
        ['_controller' => 'App\Controllers\Admin\SysConfig::' . $route[2]], 
        $route[3] ?? [], 
        ['_middleware' => ['App\Middlewares\AuthMiddleware']], 
        '', [], [$route[1]]
    ));
}

// Logs
// Login Log: /system/logs/login
$routes->add('log_login_list', new Route('/system/logs/login', 
    ['_controller' => 'App\Controllers\Admin\SysLoginLog::index'], [], ['_middleware' => ['App\Middlewares\AuthMiddleware']], '', [], ['GET']
));
$routes->add('log_login_delete', new Route('/system/logs/login/{id}', 
    ['_controller' => 'App\Controllers\Admin\SysLoginLog::destroy'], ['id' => '\d+'], ['_middleware' => ['App\Middlewares\AuthMiddleware']], '', [], ['DELETE']
));
$routes->add('log_login_batch_delete', new Route('/system/logs/login', 
    ['_controller' => 'App\Controllers\Admin\SysLoginLog::batchDestroy'], [], ['_middleware' => ['App\Middlewares\AuthMiddleware']], '', [], ['DELETE']
));
$routes->add('log_login_clean', new Route('/system/logs/login/clean', 
    ['_controller' => 'App\Controllers\Admin\SysLoginLog::clean'], [], ['_middleware' => ['App\Middlewares\AuthMiddleware']], '', [], ['DELETE']
));

// Operate Log: /system/logs/operate
$routes->add('log_oper_list', new Route('/system/logs/operate', 
    ['_controller' => 'App\Controllers\Admin\SysAccessLog::index'], [], ['_middleware' => ['App\Middlewares\AuthMiddleware']], '', [], ['GET']
));
$routes->add('log_oper_delete', new Route('/system/logs/operate/{id}', 
    ['_controller' => 'App\Controllers\Admin\SysAccessLog::destroy'], ['id' => '\d+'], ['_middleware' => ['App\Middlewares\AuthMiddleware']], '', [], ['DELETE']
));
$routes->add('log_oper_batch_delete', new Route('/system/logs/operate', 
    ['_controller' => 'App\Controllers\Admin\SysAccessLog::batchDestroy'], [], ['_middleware' => ['App\Middlewares\AuthMiddleware']], '', [], ['DELETE']
));
$routes->add('log_oper_clean', new Route('/system/logs/operate/clean', 
    ['_controller' => 'App\Controllers\Admin\SysAccessLog::clean'], [], ['_middleware' => ['App\Middlewares\AuthMiddleware']], '', [], ['DELETE']
));

// =========================================================================
// Test Routes
// =========================================================================

$routes->add('test_circuit', new Route('/test/circuit', [
    '_controller' => 'App\Controllers\Test::circuitAction'
]));

$routes->add('test_healthy', new Route('/test/healthy', [
    '_controller' => 'App\Controllers\Test::healthyAction'
]));

return $routes;