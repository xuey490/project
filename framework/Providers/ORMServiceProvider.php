<?php

declare(strict_types=1);

namespace Framework\Providers;

use Framework\Container\ServiceProviderInterface;
use Framework\Utils\ORMFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Psr\Log\LoggerInterface;

final class ORMServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $dbConfig = require BASE_PATH . '/config/database.php';
        $ormType  = $dbConfig['engine'] ?? 'think';




        // 注册 ORMFactory
        $services->set(ORMFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service(LoggerInterface::class)->nullOnInvalid()
            ])
            ->public();

        // 别名 "orm"
        $services->set('orm', ORMFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service(LoggerInterface::class)->nullOnInvalid()
            ])
            ->public();
			
    }


	/*
	模型基类的别名，暂时不可用
	*/
	public function boot(ContainerInterface $container): void
	{
		 $engine = config('database.engine', 'eloquent');
		 
		//caches('test1', ['name' => 'mike'], 3600);
		 #dump($engine);
        // === 再进行 class_alias 切换 ORM Model ===
		if ($engine === 'eloquent') {
			class_alias(
				\Illuminate\Database\Eloquent\Model::class,
				\Framework\Utils\Model::class
			);
		}

		if ($engine === 'thinkorm') {
			class_alias(
				\think\Model::class,
				\Framework\Utils\Model::class
			);
		}	 

	}
}
