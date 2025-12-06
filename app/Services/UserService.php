<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Services;

use PDO;

use Framework\Basic\BaseService;

class UserService extends BaseService
{
    /*
    private $pdo;

    public function __construct(
        \PDO $pdo // ← 类型声明必须是 \PDO
    ) {}
    */

    // 示例方法：通过数据库获取用户
    public function getUsers(int $id): array
    {
        // $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        // $stmt->execute(['id' => $id]);
        // return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        return ['1', 'test'];
    }
}
