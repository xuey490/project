<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @Filename: AttributeInjector.php
 */

namespace Framework\DI;

use Framework\Core\App;
use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Inject;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

class AttributeInjector
{
    /**
     * 反射元数据缓存，避免重复反射同一个类
     * 结构: [ ClassName => [ [property_name, attribute_instance, type_name, reflection_property], ... ] ]
     */
    protected static array $metadataCache = [];

    /**
     * 对已经实例化的对象进行属性注入
     * 
     * @param object $instance 需要注入的对象实例
     */
    public static function inject(object $instance): void
    {
        $className = get_class($instance);

        // 1. 获取注入元数据 (如果缓存中没有，先解析)
        if (!isset(self::$metadataCache[$className])) {
            self::$metadataCache[$className] = self::parseMetadata($className);
        }

        // 2. 遍历元数据，进行注入
        foreach (self::$metadataCache[$className] as $meta) {
            /** @var ReflectionProperty $reflectionProperty */
            $reflectionProperty = $meta['reflection'];
            $propertyName = $meta['property'];
            
            // 如果属性已经初始化且不为 null (例如构造函数中手动赋值了)，则跳过
            // isInitialized 需要 PHP 7.4+
            if ($reflectionProperty->isInitialized($instance) && $reflectionProperty->getValue($instance) !== null) {
                continue;
            }

            // 解析依赖值
            $value = self::resolveDependency($meta['attr'], $meta['type']);

            // 赋值
            if ($value !== null) {
                $reflectionProperty->setValue($instance, $value);
            }
        }
    }

    /**
     * 解析类的属性元数据
     */
    protected static function parseMetadata(string $className): array
    {
        $metaList = [];
        try {
            $reflection = new ReflectionClass($className);

            // 获取所有属性 (包括 private, protected, public)
            // 循环父类以确保继承的私有属性也能处理（如果需要），这里简化为处理当前类及父类可见属性
            foreach ($reflection->getProperties() as $property) {
                // 获取属性上的 Attribute
                $attributes = $property->getAttributes();
                
                foreach ($attributes as $attribute) {
                    $inst = $attribute->newInstance();

                    // 只处理 Inject 和 Autowire
                    if ($inst instanceof Inject || $inst instanceof Autowire) {
                        $property->setAccessible(true); // 允许访问 protected/private

                        $metaList[] = [
                            'reflection' => $property,
                            'property'   => $property->getName(),
                            'attr'       => $inst,
                            'type'       => $property->getType()?->getName(), // 获取属性类型声明
                        ];
                        // 一个属性只处理一个注入注解，处理完即跳出内层循环
                        break;
                    }
                }
            }
        } catch (ReflectionException $e) {
            // 理论上不会发生，除非类名不存在
            throw new RuntimeException("Reflection failed for class {$className}: " . $e->getMessage());
        }

        return $metaList;
    }

    /**
     * 根据注解类型解析依赖
     */
    protected static function resolveDependency(object $attr, ?string $type): mixed
    {
        // 1. 处理 #[Inject]
        if ($attr instanceof Inject) {
            // 如果指定了 id 则用 id，否则尝试用属性类型
            $serviceId = $attr->id ?? $type;
            
            if (!$serviceId) {
                throw new RuntimeException("Cannot inject property without type or ID.");
            }

            // 使用 App::has / App::get 安全获取
            if (App::has($serviceId)) {
                return App::get($serviceId);
            }
            
            // 如果容器里没有找到 ID，且提供了类型，尝试自动 Make (可选策略)
            // if (class_exists($serviceId)) { return App::make($serviceId); }

            return null; // 或者抛出异常，视需求而定
        }

        // 2. 处理 #[Autowire]
        if ($attr instanceof Autowire) {
            if (!$type) {
                throw new RuntimeException("Autowire requires a typed property.");
            }
            
            // 自动装配直接使用类型名去容器找
            if (App::has($type)) {
                return App::get($type);
            }

            // 如果容器没注册这个类型，尝试用 make 自动创建
            return App::make($type);
        }

        return null;
    }
}