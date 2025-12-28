<?php

declare(strict_types=1);

namespace Framework\Tenant;

use RuntimeException;
use Framework\Core\App;
use think\facade\Session;
use think\facade\Cookie;
use Illuminate\Support\Str;

/**
 * Class Tenant
 * 租户核心类（支持多租户场景，提供租户ID、租户信息的统一管理）
 * 适配 BaseRepository 自动租户筛选，支持从Cookie/Session/请求头/配置获取租户信息
 * @package Framework\Tenant
 */
class Tenant
{
    /**
     * 租户ID（核心标识，支持字符串/整数类型，如：雪花ID、自增ID、租户编号）
     * @var string|int|null
     */
    protected mixed $tenantId = null;

    /**
     * 租户信息数组（存储租户名称、域名、状态等扩展信息）
     * @var array
     */
    protected array $tenantInfo = [];

    /**
     * 租户标识存储键名（Cookie/Session 存储时使用，可自定义）
     * @var string
     */
    protected string $tenantKey = 'tenant_id';

    /**
     * 租户信息缓存标识（避免重复查询数据库）
     * @var string
     */
    protected string $cacheKey = 'tenant_info_';

    /**
     * 缓存过期时间（秒，默认3600秒=1小时）
     * @var int
     */
    protected int $cacheExpire = 3600;

    /**
     * Tenant 构造函数
     * 初始化时自动加载租户信息（优先级：请求头 > Session > Cookie > 配置 > NULL）
     */
    public function __construct()
    {
        $this->loadTenant();
    }

    /**
     * 加载租户信息（自动识别租户来源，优先级从高到低）
     * @return void
     */
    protected function loadTenant(): void
    {
		//0. 从固定函数中加载
		$this->loadSetting();

        // 1. 从HTTP请求头获取（适用于前后端分离、微服务场景，优先级最高）
        $this->loadTenantFromHeader();

        // 2. 若请求头无租户信息，从Session获取（适用于传统会话场景）
        if (is_null($this->tenantId)) {
            $this->loadTenantFromSession();
        }

        // 3. 若Session无租户信息，从Cookie获取（适用于免登录场景、持久化租户标识）
        if (is_null($this->tenantId)) {
            $this->loadTenantFromCookie();
        }

        // 4. 若以上均无，从配置文件获取（适用于单租户场景，默认租户）
        if (is_null($this->tenantId)) {
            $this->loadTenantFromConfig();
        }

        // 5. 加载租户扩展信息（若租户ID存在）
        if (!is_null($this->tenantId)) {
            $this->loadTenantInfo();
        }
    }


	/**
	 * 从固定函数中加载
	 * 
	 * 
	 */
	protected function loadSetting(): void
	{

		try {
			
			$tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;

			// 验证租户ID有效性（非空、非空白字符串，原有逻辑保留）
			if (!empty($tenantId) && trim((string)$tenantId) !== '') {
				$cleanTenantId = trim((string)$tenantId);
				// 数字类型转为int，非数字保留字符串格式（支持雪花ID/字符串租户编号）
				$this->tenantId = is_numeric($cleanTenantId) ? (int)$cleanTenantId : $cleanTenantId;
			}
		} catch (\Exception $e) {
			// 捕获所有异常，确保程序不崩溃，租户ID置为null
			$this->tenantId = null;
		}
	}



	/**
	 * 从HTTP请求头加载租户ID
	 * 默认请求头：X-Tenant-Id（可自定义修改）
	 * 优先兼容 Symfony Request 组件，同时保留 ThinkPHP/Laravel/原生PHP 兼容
	 * @return void
	 */
	protected function loadTenantFromHeader(): void
	{

		try {
			$tenantId = null;

			// 优先级1：优先兼容 Symfony Request 组件（核心改造点）
			if (class_exists('\Symfony\Component\HttpFoundation\Request')) {
				// 方式1：从当前请求实例中获取（推荐，依赖容器/全局请求对象）
				
				try {
					// 从应用容器获取 Symfony Request 实例（单例，全局共享）
					$request = App()->make('\Symfony\Component\HttpFoundation\Request');
					// Symfony Request 获取请求头：支持驼峰式（X-Tenant-Id）或下划线式（HTTP_X_TENANT_ID）
					$tenantId = $request->headers->get('X-Tenant-Id');
				} catch (\Exception $e) {
					// 方式2：若容器中无实例，从全局静态方法获取（Symfony 全局请求）
					if (method_exists('\Symfony\Component\HttpFoundation\Request', 'createFromGlobals')) {
						$globalRequest = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
						$tenantId = $globalRequest->headers->get('X-Tenant-Id');
					}
				}

				#$tenantId= (string)(app('request')->headers->get('X-Tenant-Id'));
			}
			// 优先级2：原生PHP兼容（原有逻辑保留，兜底方案）
			else {
				$tenantId = (string)$_SERVER['HTTP_X_TENANT_ID'] ?? null;
			}


			// 验证租户ID有效性（非空、非空白字符串，原有逻辑保留）
			if (!empty($tenantId) && trim((string)$tenantId) !== '') {
				$cleanTenantId = trim((string)$tenantId);
				// 数字类型转为int，非数字保留字符串格式（支持雪花ID/字符串租户编号）
				$this->tenantId = is_numeric($cleanTenantId) ? (int)$cleanTenantId : $cleanTenantId;
			}
		} catch (\Exception $e) {
			// 捕获所有异常，确保程序不崩溃，租户ID置为null
			$this->tenantId = null;
		}
	}

    /**
     * 从Session加载租户ID
     * @return void
     */
    protected function loadTenantFromSession(): void
    {
        try {
			$tenantId = app('session')->get($this->tenantKey);
			
            if (!empty($tenantId)) {
                $this->tenantId = is_numeric($tenantId) ? (int)$tenantId : $tenantId;
            }
        } catch (\Exception $e) {
            $this->tenantId = null;
        }
    }


	/**
	 * 从Cookie加载租户ID
	 * 优先兼容 Symfony Request 组件，同时保留 ThinkPHP/原生PHP 兼容
	 * @return void
	 */
	protected function loadTenantFromCookie(): void
	{
		try {
			$tenantId = null;

			// 优先级1：优先兼容 Symfony Request 组件（核心改造点）
			if (class_exists('\Symfony\Component\HttpFoundation\Request')) {
				try {
					// 方式1：从应用容器获取 Symfony Request 单例实例（推荐，依赖注入规范）
					$request = App()->make('\Symfony\Component\HttpFoundation\Request');
					// Symfony Request 获取Cookie：通过cookies属性的get方法，第二个参数为默认值null
					$tenantId = $request->cookies->get($this->tenantKey, null);
				} catch (\Exception $e) {
					// 方式2：兜底方案，创建全局Symfony Request实例获取Cookie
					$globalRequest = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
					$tenantId = $globalRequest->cookies->get($this->tenantKey, null);
				}
			}
			// 优先级2：原生PHP Cookie（兜底方案，原有逻辑保留）
			else {
				$tenantId = $_COOKIE[$this->tenantKey] ?? null;
			}

			// 验证租户ID有效性并赋值（优化类型安全处理）
			if (!empty($tenantId) && trim((string)$tenantId) !== '') {
				$cleanTenantId = trim((string)$tenantId);
				// 数字类型转为int，非数字保留字符串格式（支持雪花ID/字符串租户编号）
				$this->tenantId = is_numeric($cleanTenantId) ? (int)$cleanTenantId : $cleanTenantId;
			}
		} catch (\Exception $e) {
			// 捕获所有异常，避免程序崩溃，租户ID置为null
			$this->tenantId = null;
		}
	}

    /**
     * 从配置文件加载租户ID（单租户默认配置）
     * 配置文件路径：config/tenant.php（可自定义修改）
     * @return void
     */
    protected function loadTenantFromConfig(): void
    {
        try {			
			$tenantId	= config('tenant.default_tenant_id', 1);

            if (!empty($tenantId)) {
                $this->tenantId = is_numeric($tenantId) ? (int)$tenantId : $tenantId;
            }
        } catch (\Exception $e) {
            $this->tenantId = null;
        }
    }

    /**
     * 加载租户扩展信息（从数据库/缓存获取）
     * 可根据项目需求重写此方法，自定义租户信息查询逻辑
     * @return void
     */
    protected function loadTenantInfo(): void
    {
        // 拼接缓存键名
        $cacheKey = $this->cacheKey . $this->tenantId;

        try {
            // 1. 先从缓存获取租户信息（提升性能）
            $tenantInfo = $this->getCache($cacheKey);

            // 2. 缓存不存在时，从数据库查询
            if (empty($tenantInfo)) {
                $tenantInfo = $this->queryTenantInfoFromDb();

                // 3. 将查询结果存入缓存
                if (!empty($tenantInfo)) {
                    $this->setCache($cacheKey, $tenantInfo, $this->cacheExpire);
                }
            }

            // 4. 赋值租户信息
            $this->tenantInfo = $tenantInfo ?: [];
        } catch (\Exception $e) {
            $this->tenantInfo = [];
            // 可添加日志记录：\think\facade\Log::error("加载租户信息失败：{$e->getMessage()}");
        }
    }

    /**
     * 从数据库查询租户信息（需根据项目实际表结构修改）
     * 默认假设租户表：tenant，字段：id, name, domain, status, created_at, updated_at
     * @return array 租户信息数组
     */
    protected function queryTenantInfoFromDb(): array
    {
        // 兼容 ThinkPHP Db
        if (class_exists('\think\facade\Db')) {
            return \think\facade\Db::table('tenant')
                ->where('id', $this->tenantId)
                ->where('status', 1) // 只查询启用状态的租户
                ->find() ?: [];
        }
        // 兼容 Illuminate Db
        elseif (class_exists('\Illuminate\Database\Capsule\Manager')) {
            $tenant = \Illuminate\Database\Capsule\Manager::table('tenant')
                ->where('id', $this->tenantId)
                ->where('status', 1)
                ->first();
            return $tenant ? (array)$tenant : [];
        } else {
            return [];
        }
    }

    /**
     * 获取租户ID（BaseRepository 调用此方法获取租户标识）
     * @return string|int|null 租户ID（无租户时返回NULL）
     */
    public function getId(): mixed
    {
        return $this->tenantId;
    }

    /**
     * 设置租户ID（手动切换租户时使用）
     * @param string|int|null $tenantId 租户ID
     * @param bool $save 是否保存到Session/Cookie（默认true）
     * @return $this
     */
    public function setId(mixed $tenantId, bool $save = true): self
    {
        $this->tenantId = $tenantId;

        // 保存租户ID到Session/Cookie（持久化）
        if ($save && !is_null($tenantId)) {
            $this->saveTenantId($tenantId);
        }

        // 重新加载租户信息
        $this->loadTenantInfo();

        return $this;
    }

    /**
     * 保存租户ID到Session/Cookie
     * @param string|int $tenantId 租户ID
     * @return void
     */
    protected function saveTenantId(mixed $tenantId): void
    {
        try {
            // 保存到Session
			app('session')->set($this->tenantKey , $tenantId);

            // 保存到Cookie（有效期7天，可自定义）
            setcookie($this->tenantKey, $tenantId, time() + 3600, '/');
			
        } catch (\Exception $e) {
            // 保存失败不抛出异常，避免影响主流程
        }
    }

    /**
     * 获取租户信息（单个字段/全部信息）
     * @param string|null $key 字段名（NULL返回全部信息）
     * @param mixed $default 默认值（字段不存在时返回）
     * @return mixed 租户信息
     */
    public function getInfo(?string $key = null, mixed $default = null): mixed
    {
        // 返回全部租户信息
        if (is_null($key)) {
            return $this->tenantInfo;
        }

        // 返回单个字段信息
        return $this->tenantInfo[$key] ?? $default;
    }

    /**
     * 设置租户信息（手动更新租户信息）
     * @param array $tenantInfo 租户信息数组
     * @param bool $saveToCache 是否保存到缓存（默认true）
     * @return $this
     */
    public function setInfo(array $tenantInfo, bool $saveToCache = true): self
    {
        $this->tenantInfo = $tenantInfo;

        // 保存到缓存
        if ($saveToCache && !is_null($this->tenantId)) {
            $cacheKey = $this->cacheKey . $this->tenantId;
            $this->setCache($cacheKey, $tenantInfo, $this->cacheExpire);
        }

        return $this;
    }

    /**
     * 获取租户名称（快捷方法）
     * @param string $default 默认名称（默认：未知租户）
     * @return string
     */
    public function getName(string $default = '未知租户'): string
    {
        return $this->getInfo('name', $default);
    }

    /**
     * 获取租户域名（快捷方法）
     * @param string $default 默认域名
     * @return string
     */
    public function getDomain(string $default = ''): string
    {
        return $this->getInfo('domain', $default);
    }

    /**
     * 检查租户是否有效（启用状态）
     * @return bool 有效返回true，无效返回false
     */
    public function isValid(): bool
    {
        // 租户ID不存在，无效
        if (is_null($this->tenantId)) {
            return false;
        }

        // 租户状态为1（启用），有效
        return $this->getInfo('status', 0) == 1;
    }

    /**
     * 清除租户信息（退出租户/切换租户时使用）
     * @return $this
     */
    public function clear(): self
    {
        // 清除租户ID
        $this->tenantId = null;

        // 清除租户信息
        $this->tenantInfo = [];

        // 清除Session/Cookie中的租户ID
        $this->removeTenantIdFromStorage();

        // 清除缓存
        $cacheKey = $this->cacheKey . $this->tenantId;
        $this->deleteCache($cacheKey);

        return $this;
    }

    /**
     * 从Session/Cookie中移除租户ID
     * @return void
     */
    protected function removeTenantIdFromStorage(): void
    {
        try {
            // 移除Session中的租户ID
            app('session')->set($this->tenantKey, null );

            // 移除Cookie中的租户ID
            setcookie($this->tenantKey, '', time() - 3600, '/');
        } catch (\Exception $e) {
            // 移除失败不抛出异常
        }
    }

    /**
     * 获取缓存（兼容多缓存驱动，可重写此方法自定义缓存实现）
     * @param string $key 缓存键名
     * @return mixed 缓存数据
     */
    protected function getCache(string $key): mixed
    {		
		return app('cache')->get($key) ?? null;
    }

    /**
     * 设置缓存（兼容多缓存驱动）
     * @param string $key 缓存键名
     * @param mixed $value 缓存数据
     * @param int $expire 过期时间（秒）
     * @return bool 设置成功返回true，失败返回false
     */
    protected function setCache(string $key, mixed $value, int $expire): bool
    {
		return app('cache')->set($key , $value , $expire);
    }

    /**
     * 删除缓存（兼容多缓存驱动）
     * @param string $key 缓存键名
     * @return bool 删除成功返回true，失败返回false
     */
    protected function deleteCache(string $key): bool
    {
        return app('cache')->delete($key); 
    }

    /**
     * 魔术方法：直接获取租户信息字段（如：$tenant->name 等价于 $tenant->getInfo('name')）
     * @param string $name 字段名
     * @return mixed 字段值
     */
    public function __get(string $name)
    {
        return $this->getInfo($name);
    }

    /**
     * 魔术方法：检查租户信息字段是否存在
     * @param string $name 字段名
     * @return bool 存在返回true，不存在返回false
     */
    public function __isset(string $name)
    {
        return isset($this->tenantInfo[$name]);
    }
}