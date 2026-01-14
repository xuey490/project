<?php

declare(strict_types=1);

/**#
 * This file is part of FssPHP Framework.
 *
 * @Filename: AttributeInjectionPass.php
 */

namespace Framework\Container\Compiler;

use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Inject;
use Framework\DI\AttributeInjector;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AttributeInjectionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // 遍历容器中定义的所有服务
        foreach ($container->getDefinitions() as $id => $definition) {
            // 跳过抽象服务、合成服务或没有类名的服务
            if ($definition->isAbstract() || $definition->isSynthetic() || !$definition->getClass()) {
                continue;
            }

            $className = $definition->getClass();

            // 简单检查类是否存在
            if (!class_exists($className)) {
                continue;
            }

            // 如果该类有注入注解，配置 "Configurator"
            if ($this->hasInjectionAttributes($className)) {
                // setConfigurator 方法告诉 Symfony 容器：
                // 当这个服务实例化完成后，请调用 AttributeInjector::inject($instance)
                $definition->setConfigurator([AttributeInjector::class, 'inject']);
            }
        }
    }

    /**
     * 快速扫描类是否有相关注解，避免不必要的 Configurator 配置
     */
    private function hasInjectionAttributes(string $className): bool
    {
        try {
            $reflection = new ReflectionClass($className);
            
            // 扫描所有属性
            foreach ($reflection->getProperties() as $property) {
                if (!empty($property->getAttributes(Inject::class)) || 
                    !empty($property->getAttributes(Autowire::class))) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // 忽略反射错误
            return false;
        }
        return false;
    }
}