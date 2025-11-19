<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Services;

class BlogService
{
    /*
    private $pdo;

    public function __construct(
        \PDO $pdo // ← 类型声明必须是 \PDO
    ) {}
    */

    // 示例方法：通过数据库获取用户
    public function getList(): array
    {
        return [
            '1'=> [
                'id'       => 1,
                'title'    => '测试标题...',
                'excerpt'  => '测试...',
                'createdAt'=> '2025-08-01',
            ],
            '2'=> [
                'id'       => 2,
                'title'    => '测试标题测试标题测试标题...',
                'excerpt'  => '测试...',
                'createdAt'=> '2025-09-01',
            ],
        ];
    }

    public function getpopularPosts(): array
    {
        return [
            '1'=> [
                'id'       => 1,
                'title'    => '热门标题。。。标题1...',
                'excerpt'  => '测试...',
                'createdAt'=> '2025-08-01',
            ],

            '2'=> [
                'id'       => 2,
                'title'    => '热门标题。。。标题2...',
                'excerpt'  => '测试...',
                'createdAt'=> '2025-08-01',
            ],
        ];
    }
}
