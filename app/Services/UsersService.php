<?php
declare(strict_types=1);

namespace App\Services;

use Framework\Basic\BaseService;
use App\Dao\UserDao;
use Framework\Core\App;
use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Autowire;

/**
 * 用户服务层
 * 封装业务逻辑，调用 DAO 层操作数据
 */
class UsersService extends BaseService
{
    /**
     * 注入 UserDao，类型声明为 ?UserDao（泛型支持下合法）
     * @var ?UserDao
     */
    ##[Autowire]
    #protected ?UserDao $dao; // 现在类型声明合法

    public function __construct()
    {
        $this->dao = App()->make(UserDao::class);
    }

    /**
     * 【基础 CURD】复用 BaseService 代理的 DAO 方法
     * 无需重复编写 get/selectList/save/update/delete 等方法
     */

    /**
     * 【扩展业务方法】创建用户并加密密码
     * @param array $data
     * @return mixed
     */
    public function createUser(array $data)
    {
        // 密码加密 业务逻辑
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        // 调用 DAO 的 save 方法（BaseService 通过 __call 代理）
        return $this->save($data);
    }

    /**
     * 【扩展业务方法】多租户用户列表查询
     * @param int $tenantId
     * @param array $where
     * @return array
     */
    public function getUserListByTenant(int $tenantId, array $where = []): array
    {
        return $this->dao->getListByTenantId($tenantId, $where);
    }
}