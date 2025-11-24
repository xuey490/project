<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Cache;

use think\facade\Cache;

/*
# thinkcache测试
#$cache = app('cache');
#$cache->set('test1', ['name' => 'mike'], 3600);
#$test1 =$cache->get('test1');
//$test1 = $cache->clear();



//cache('foo', 'bar', 120);  // set
//echo cache('foo');         // get

//$factory = new \Framework\Cache\ThinkCache(require BASE_PATH . '/config/cache.php');
//$redisCache = $factory->create('redis'); // ✅ 成功
//$redisCache ->set('foo111', 'bar', 120);

//$ca = app(\Framework\Cache\ThinkCache::class)->create('redis');
//$ca->set('xxxx', 'bar', 120);

// File
$fileCache = $factory->create('file');
$fileCache->set('foot', 'bar___11');
echo $fileCache->get('foot');


*/
class ThinkCache
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config =$config;
        Cache::config($config);
    }

    /*
    public function __construct(protected array $config = [])
    {
    }


    public function create(): ThinkAdapter
    {
        // ✅ 直接用配置创建 Cache 实例
        $cache = new Cache($this->config);

        // ✅ 再包装成框架的适配器
        return new ThinkAdapter($cache);
    }
    */

    /*
    *
    工厂支持切换 store（即选择 redis 或 file）
    $redisCache = $container->get(ThinkCache::class)->create('redis');
    $fileCache  = $container->get(ThinkCache::class)->create('file');
    *
    */
    public function create(?string $store = null): ThinkAdapter
    {
        $cache = Cache::connect($this->config['stores'][$this->config['default']]);
        if ($store) {
            $cache = Cache::store($store); // $cache->store($store);
        }
        return new ThinkAdapter($cache);
    }
}
