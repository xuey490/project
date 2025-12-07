<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */
 
namespace App\Controllers;


use Framework\Database\DatabaseFactory;
use Monolog\Logger; // 假设你使用 Monolog
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class Database
{
    public function __construct(
		private DatabaseFactory $db,	//依赖注入的写法

    ) {
		
	}	
	public function index(Request $request)
	{
		$config = [
			'default' => 'mysql',
			'connections' => [
				'mysql' => [
					'type' => 'mysql',
					'hostname' => '127.0.0.1',
					// 数据库名
					'database'           =>  'oa',
					// 用户名
					'username'           =>  'root',
					// 密码
					'password'           =>  'root',
					// 端口
					'hostport'           =>  '3306',
					// 数据库表前缀
					'prefix'             =>  'oa_',
					'debug'    => true, // 控制是否记录日志
				]
			]
		];

		$logger = new Logger('db'); // 你的 PSR-3 Logger
		
		// 2. 配置文件处理器（关键：指定具体的日志文件路径）
		// 推荐路径：项目根目录下的 storage/logs 文件夹（需确保该文件夹有写入权限）
		$logFilePath = __DIR__ . '/../../storage/logs/db.log'; // 适配你的控制器路径
		// 第二个参数是日志级别（DEBUG 会记录所有级别日志，INFO 只记录 INFO 及以上）
		$handler = new StreamHandler($logFilePath, Logger::DEBUG);

		// 3. 将处理器添加到日志器
		$logger->pushHandler($handler);

		// 2. 实例化工厂 (无缝切换 'thinkORM' 或 'laravelORM')
		$dbFactory = new DatabaseFactory($config, 'laravelORM', $logger);	//原地初始化写法

		// 3. 使用
		// 方式 A: 类名
		$userModel = ($this->db)(App\Models\Config::class); 

		// 方式 B: 表名 (Eloquent模式下返回 Builder, Think模式下返回 Query)
		$users = $dbFactory->make('config')->where('id', '>', 1)->get();
		dump($users);
		return new Response('database test!');
	}

}