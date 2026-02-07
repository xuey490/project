<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Permission.php
 * @Date: 2026-2-7
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

declare(strict_types=1);

namespace Framework\Casbin;

use Casbin\Enforcer;
use Casbin\Exceptions\CasbinException;
use Casbin\Log\Logger\DefaultLogger;
use Casbin\Model\Model;
use Framework\Casbin\Watcher\RedisWatcher;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Exception;

/**
 * @see \Casbin\Enforcer
 * @mixin Enforcer
 */
/**
 * @see \Casbin\Enforcer
 * @mixin Enforcer
 * @method static bool enforce(mixed ...$rvals) 权限检查，输入参数通常是(sub, obj, act)
 * @method static bool addPolicy(mixed ...$params) 当前策略添加授权规则
 * @method static bool addPolicies(mixed ...$params) 当前策略添加授权规则
 * @method static bool hasPolicy(mixed ...$params) 确定是否存在授权规则
 * @method static bool removePolicy(mixed ...$params) 当前策略移除授权规则
 * @method static array getAllRoles() 获取所有角色
 * @method static array getPolicy() 获取所有的角色的授权规则
 * @method static bool updatePolicies(array $oldPolices, array $newPolicies) 更新策略
 * @method static bool removePolicies(array $rules) 删除策略
 * @method static array getRolesForUser(string $name, string ...$domain) 获取用户具有的角色
 * @method static array getUsersForRole(string $name, string ...$domain) 获取具有角色的用户
 * @method static bool hasRoleForUser(string $name, string $role, string ...$domain) 确定用户是否具有角色
 * @method static bool addRoleForUser(string $user, string $role, string ...$domain) 给用户添加角色
 * @method static bool addRolesForUser(string $user, array $roles, string ...$domain)
 * @method static bool addPermissionForUser(string $user, string ...$permission) 赋予权限给某个用户或角色
 * @method static bool addPermissionsForUser(string $user, array ...$permissions) 赋予用户或角色多个权限。 如果用户或角色已经有一个权限，则返回 false (不会受影响)
 * @method static bool deleteRoleForUser(string $user, string $role, string ...$domain) 删除用户的角色
 * @method static bool deleteUser(string $user) 删除用户
 * @method static bool deleteRolesForUser(string $user, string ...$domain) 删除某个用户的所有角色
 * @method static bool deleteRole(string $role) 删除单个角色
 * @method static bool deletePermission(string ...$permission) 删除权限
 * @method static bool deletePermissionForUser(string $name, string ...$permission) 删除用户或角色的权限。如果用户或角色没有权限则返回 false(不会受影响)。
 * @method static bool deletePermissionsForUser(string $name) 删除用户或角色的权限。如果用户或角色没有任何权限（也就是不受影响），则返回false。
 * @method static array getPermissionsForUser(string $name) 获取用户或角色的所有权限
 * @method static bool hasPermissionForUser(string $user, string ...$permission) 决定某个用户是否拥有某个权限
 * @method static array getImplicitRolesForUser(string $name, string ...$domain) 获取用户具有的隐式角色
 * @method static array getImplicitPermissionsForUser(string $username, string ...$domain) 获取用户具有的隐式权限
 * @method static array getImplicitUsersForRole(string $name, string ...$domain) 获取具有隐式用户的角色
 * @method static array getImplicitResourcesForUser(string $user, string ...$domain) 获取具有隐式资源的用户
 * @method static array getImplicitUsersForPermission(string ...$permission) 获取隐式用户的权限
 * @method static array getAllUsersByDomain(string $domain) 获取域中的所有用户
 * @method static array getUsersForRoleInDomain(string $name, string $domain) 获取在域内具有传入角色的用户
 * @method static array getRolesForUserInDomain(string $name, string $domain) 获取域中用户具有的所有角色
 * @method static array getPermissionsForUserInDomain(string $name, string $domain) 获取域中用户具有的所有权限
 * @method static bool addRoleForUserInDomain(string $user, string $role, string $domain) 给域中的用户添加角色
 * @method static bool deleteRoleForUserInDomain(string $user, string $role, string $domain) 删除域中用户的角色
 * @method static bool deleteRolesForUserInDomain(string $user, string $domain) 删除域中用户的所有角色
 * @method static bool deleteAllUsersByDomain(string $domain) 删除域中的所有用户
 * @method static bool deleteDomains(string ...$domain) 删除域
 * @method static bool addFunction(string $name, \Closure $func) 添加一个自定义函数
 */
class Permission
{
    /** 
     * 存储不同驱动的 Enforcer 实例
     * @var Enforcer[] $_manager 
     */
    protected static array $_manager = [];

    /**
     * 存储 Redis Watcher 实例（用于发布通知）
     * @var RedisWatcher[] $_watchers
     */
    protected static array $_watchers = [];

    /**
     * 获取指定驱动的 Enforcer 实例
     * 
     * @param string|null $driver 驱动名称
     * @return Enforcer
     * @throws CasbinException
     * @author Tinywan(ShaoBo Wan)
     */
	public static function driver(?string $driver = null): Enforcer
	{
		$driver = $driver ?? self::getDefaultDriver();

		if (!is_string($driver)) {
			throw new RuntimeException("驱动名称必须是字符串");
		}

		if (isset(static::$_manager[$driver])) {
			return static::$_manager[$driver];
		}

		$config = self::getConfig("enforcers.{$driver}");

		if (empty($config) || !is_array($config)) {
			throw new RuntimeException("Casbin 驱动配置错误: {$driver}");
		}

		/*
		|--------------------------------------------------------------------------
		| 1. Load Model
		|--------------------------------------------------------------------------
		*/
		$model = new Model();
		$modelConfig = $config['model'] ?? [];
		switch ($modelConfig['config_type'] ?? '') {
			case 'file':
				$modelPath = $modelConfig['config_file_path'] ?? '';
				if (!$modelPath || !file_exists($modelPath)) {
					throw new RuntimeException("Casbin 模型文件不存在: {$modelPath}");
				}
				$model->loadModel($modelPath);
				break;
			case 'text':
				$text = $modelConfig['config_text'] ?? '';
				if (!$text) {
					throw new RuntimeException("Casbin model text 为空");
				}
				$model->loadModelFromText($text);
				break;
			default:
				throw new RuntimeException("未知 model 类型");
		}

		/*
		|--------------------------------------------------------------------------
		| 2. Logger（核心修复）
		|--------------------------------------------------------------------------
		*/

		$logConfig = self::getConfig('log', []);
		$appLogger = null;   // ⭐ 给业务使用（Monolog）
		$casbinLogger = null; // ⭐ 给 Casbin 使用
		if (($logConfig['enabled'] ?? false) === true) {
			$loggerName = $logConfig['logger'] ?? 'casbin';
			$logPath = $logConfig['path'] ?? __DIR__.'/../../logs/casbin.log';
			if (!is_dir(dirname($logPath))) {
				mkdir(dirname($logPath), 0755, true);
			}
			// ⭐ Monolog logger（业务 logger）
			$appLogger = new Logger($loggerName);
			$appLogger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));

			// ⭐ Casbin logger（内部）
			$casbinLogger = new DefaultLogger($appLogger);
		}

		/*
		|--------------------------------------------------------------------------
		| 3. Adapter
		|--------------------------------------------------------------------------
		*/
		$adapterClass = $config['adapter'] ?? '';
		if (!$adapterClass || !class_exists($adapterClass)) {
			throw new RuntimeException("Adapter 不存在: {$adapterClass}");
		}
		$adapter = new $adapterClass($driver);
		
		/*
		|--------------------------------------------------------------------------
		| 4. Create Enforcer
		|--------------------------------------------------------------------------
		*/
		$enforcer = new Enforcer(
			$model,
			$adapter,
			$casbinLogger,
			$logConfig['enabled'] ?? false
		);

		/*
		|--------------------------------------------------------------------------
		| 5. Redis Watcher
		|--------------------------------------------------------------------------
		*/
		$redisWatcherConfig = $config['redis_watcher'] ?? [];

		if (($redisWatcherConfig['enable'] ?? false) === true) {

			$redisConfig = [
				'host' => $redisWatcherConfig['host'] ?? '127.0.0.1',
				'port' => $redisWatcherConfig['port'] ?? 6379,
				'password' => $redisWatcherConfig['password'] ?? '',
				'database' => $redisWatcherConfig['database'] ?? 0,
				'channel' => $redisWatcherConfig['channel'] ?? '/casbin',
				'timeout' => $redisWatcherConfig['timeout'] ?? 5.0,
			];

			$watcher = new RedisWatcher(
				$redisConfig,
				$driver,
				$appLogger // ⭐ 这里用 Monolog
			);

			$watcher->setUpdateCallback(function () use ($driver, $appLogger) {

				if (isset(static::$_manager[$driver])) {

					static::$_manager[$driver]->loadPolicy();

					if ($appLogger) {
						$appLogger->info('Casbin policy reload by redis watcher');
					}
				}
			});

			$enforcer->setWatcher($watcher);

			static::$_watchers[$driver] = $watcher;
		}

		/*
		|--------------------------------------------------------------------------
		| 6. Load Policy
		|--------------------------------------------------------------------------
		*/

		$enforcer->loadPolicy();

		static::$_manager[$driver] = $enforcer;

		return $enforcer;
	}


    /**
     * 启动 Redis Watcher 订阅监听（阻塞式）
     * 
     * @param string|null $driver 驱动名称
     * @throws CasbinException|Exception
     */
    public static function startWatcherListening(?string $driver = null): void
    {
        // 修复：确保 driver 是字符串
        $driver = $driver ?? self::getDefaultDriver();
        if (!is_string($driver)) {
            throw new RuntimeException("驱动名称必须是字符串，当前类型：" . gettype($driver));
        }
        
        // 确保 Enforcer 已初始化
        self::driver($driver);
        
        if (isset(static::$_watchers[$driver])) {
            echo "开始监听 Casbin 策略更新（驱动：{$driver}）...\n";
            static::$_watchers[$driver]->startListening();
        } else {
            throw new RuntimeException("Redis Watcher 未初始化（驱动：{$driver}），请检查 redis_watcher.enable 配置");
        }
    }

    /**
     * 手动发布策略更新通知
     * 
     * @param string|null $driver 驱动名称
     * @throws CasbinException|Exception
     */
    public static function publishPolicyUpdate(?string $driver = null): void
    {
        $driver = $driver ?? self::getDefaultDriver();
        if (!is_string($driver)) {
            throw new RuntimeException("驱动名称必须是字符串，当前类型：" . gettype($driver));
        }
        
        if (isset(static::$_watchers[$driver])) {
            static::$_watchers[$driver]->update();
        } else {
            throw new RuntimeException("Redis Watcher 未初始化: {$driver}");
        }
    }

    /**
     * 获取所有驱动的 Enforcer 实例
     * 
     * @return Enforcer[]
     */
    public static function getAllDriver(): array
    {
        return static::$_manager;
    }

    /**
     * 获取默认驱动名称（核心修复：确保返回字符串）
     * 
     * @return string
     */
    public static function getDefaultDriver(): string
    {
        $default = self::getConfig('default', 'default');
        // 强制转为字符串，避免返回数组
        return is_string($default) ? $default : 'default';
    }

    /**
     * 通用配置读取方法（核心修复：适配实际配置结构）
     * 
     * @param string|null $name 配置名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getConfig(?string $name = null, $default = null): mixed
    {
        // 加载配置文件（确保配置结构正确）
        static $config = null;
        if ($config === null) {
            $configFile = config_path() . '/permission.php'; // 你的配置文件路径
            if (file_exists($configFile)) {
                $config = require $configFile;
            } else {
                // 默认配置
                $config = [
                    'default' => 'default',
                    'log' => ['enabled' => false],
                    'enforcers' => [
                        'default' => [
                            'model' => [
                                'config_type' => 'file',
                                'config_file_path' => __DIR__ . '/../../config/casbin-model.conf',
                            ],
                            'adapter' => \Framework\Casbin\Adapter\LaravelDatabaseAdapter::class, // 匹配你的适配器命名空间
							// 数据库设置
							'database' => [
								'connection' => 'mysql',
								'rules_table' => 'casbin_rule',
								'rules_name' => null
							],		
                            'redis_watcher' => [
                                'enable' => true,
                                'host' => '127.0.0.1',
                                'port' => 6379,
                                'password' => '',
                                'database' => 0,
                                'channel' => '/casbin',
                            ],
                        ],
                    ],
                ];
            }
        }

        // 修复：配置读取逻辑（支持多级配置）
        if (is_null($name) || $name === '') {
            return $config['default'] ?? $default;
        }

        // 处理多级配置（如 enforcers.default.model）
        $keys = explode('.', $name);
        $value = $config;
        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * 静态方法调用转发
     * 
     * @param string $method 方法名
     * @param array $arguments 参数
     * @return mixed
     * @throws CasbinException
     */
    public static function __callStatic(string $method, $arguments)
    {
        try {
            $result = self::driver()->{$method}(...$arguments);
            
            // 自动发布策略更新通知
            $policyMethods = [
                'addPolicy', 'addPolicies', 'removePolicy', 'removePolicies',
                'updatePolicy', 'updatePolicies', 'deleteRole', 'deletePermission'
            ];
            if (in_array($method, $policyMethods) && $result === true) {
                self::publishPolicyUpdate();
            }
            
            return $result;
        } catch (Exception $e) {
            // 友好的错误提示
            throw new RuntimeException("Casbin 方法调用失败 [{$method}]: " . $e->getMessage(), 0, $e);
        }
    }
}