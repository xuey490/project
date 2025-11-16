<?php

declare(strict_types=1);

/**
 * This file is part of NavaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Providers;

use Framework\Container\ServiceProviderInterface;
use Framework\Utils\FileUploader;
use Framework\Utils\MimeTypeChecker;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
* 注册文件上传器
*/
final class UploadServiceProvider implements ServiceProviderInterface
{
    // public function __invoke(ContainerConfigurator $configurator): void
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 注册 MIME 检查器
        $services->set(MimeTypeChecker::class)
            ->args([BASE_PATH . '/config/mime_types.php'])->public();

        // 注册文件上传器，注入上传配置 + MIME 检查器
        $uploadConfig = include BASE_PATH . '/config/upload.php';

        $services->set(FileUploader::class)
            ->args([$uploadConfig, service(MimeTypeChecker::class)])->public();
    }

    public function boot(ContainerInterface $container): void
    # public function boot(ContainerConfigurator $container): void
    {}
}
