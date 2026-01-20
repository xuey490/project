<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Services;

use PDO;

use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Context;

use App\Repository\ModuleRepository;
use App\Dao\NoticeDao;
use App\Services\BlogService;

use Framework\Basic\BaseService;

class UserService extends BaseService
{
    /*
    private $pdo;

    public function __construct(
        \PDO $pdo // ← 类型声明必须是 \PDO
    ) {}
    */
    #[Autowire]
    protected BlogService $moduleRe;

	
    /**
     * 子类可根据需要覆盖 lifecycle
     */
    protected function initialize(): void
    {
		//$this->dao = app('App\Dao\NoticeDao');
    }


    // 示例方法：通过数据库获取用户
    public function getUsers(int $id): array
    {
		dump($this->moduleRe);
        // $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        // $stmt->execute(['id' => $id]);
        // return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        return ['1', 'test'];
    }
}
