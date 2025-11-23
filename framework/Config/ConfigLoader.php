<?php

declare(strict_types=1);

/**
 * This file is part of project Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Config;

/*
* 加载单个文件配置
*
* new \Framework\Config\ConfigLoader( $configFile );
* $data = $ConfigInit->loadAll();
*/
class ConfigLoader
{
    private ?array $cachedConfig = null;
	
	private string $configFile;

    public function __construct(
        string $configFile
    ) {
        $this->configFile = $configFile;
    }

    /**
     * 加载所有配置文件（带缓存）.
     */
    public function loadAll(): array
    {
        if ($this->cachedConfig !== null) {
            return $this->cachedConfig;
        }

        $config = [];
		
		if(file_exists($this->configFile)&& is_file($this->configFile)){
			$config = require $this->configFile;
		}

        return $this->cachedConfig = $config;
    }

    /**
     * 获取某个配置项（支持点语法：database.host）.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $config = $this->loadAll();
        $keys   = explode('.', $key);
        $value  = $config;

        foreach ($keys as $k) {
            if (is_array($value) && array_key_exists($k, $value)) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * 手动清除缓存（用于开发环境热重载）.
     */
    public function clearCache(): void
    {
        $this->cachedConfig = null;
    }
}
