<?php
// config/services.php
// 这个是个核心的配置文件，如果不懂，请参考symfony服务注册器的语法或下面的例子

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Framework\Container\ContainerProviders;

	use Framework\Factory\ThinkORMFactory;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    // 默认配置
    $services
        ->defaults()
        ->autowire()      // 所有服务默认自动装配
        ->autoconfigure() // 所有服务默认自动配置
		->public();
		
	#(new SessionServiceProvider())($configurator);

    $services->set('test', \stdClass::class)->public();

	// 注册事件分发
    $services->set(\Framework\Event\Dispatcher::class)
        ->arg('$container', service('service_container'))->public(); // ✅ 显式注入容器自身 注意arg，跟args差异

	$databseConfig  = require BASE_PATH . '/config/database.php';

    // === 注册 ThinkORM 模型工厂服务 ===
	/*
    $services
        ->set(\Framework\Utils\ThinkORMFactory::class)
		->args([$databseConfig])
        ->public(); // 可选：设为 public 以便直接从容器获取

    // 可以额外 alias
    $services->alias('orm', \Framework\Utils\ThinkORMFactory::class);
	*/
	

	

    // ✅ 1. 自动加载应用 Provider
    $providerManager = new ContainerProviders();

	// ✅ 2. 自动加载核心 + 应用 Provider
	$providerManager->loadAll(
		$configurator,
		'App\\Providers\\',
		BASE_PATH . '/app/Providers'
		
	);

    // ✅ 3. 启动所有 Provider（boot）
    $providerManager->bootProviders($configurator);
	
    // 工厂服务
	$services->set(ThinkORMFactory::class)
		->args([
			$databseConfig,
			service('log'),        // SQL 日志
			service('log'),        // 慢 SQL 日志
			200
		])
		->public();

	// DbManager 使用工厂创建
	$services->set(\think\DbManager::class)
		->factory([ service(ThinkORMFactory::class), 'create' ])
		->public();

	// 你框架里的统一 service id
	$services->alias('thinkorm', \think\DbManager::class);
	
	
	

};
