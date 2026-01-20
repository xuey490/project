<?php
declare(strict_types=1);

namespace App\Services;

use Framework\Basic\BaseService;
use App\Dao\UserDao;
use Framework\Core\App;
use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Autowire;
use Framework\Basic\BaseDao; // 引入父类类型

/**
 * 用户服务层
 * @extends BaseService<UserDao> // 指定泛型类型为 UserDao
 */
class UsersService extends BaseService
{
    /**
     * 注入 UserDao，类型声明为 ?UserDao（泛型支持下合法）
     * @var ?UserDao
     */
    ##[Autowire]
    #protected ?UserDao $dao; // 现在类型声明合法

    // 容器会自动注入 UserDao
	/*
    public function __construct(UserDao $dao)
    {
        // 1. 赋值给父类的 $this->dao 属性
        $this->dao = $dao;

        // 2. 【必须】调用父类构造函数
        // 虽然父类构造函数没有参数，但必须调用以执行 $this->inject() 等逻辑
        parent::__construct();
    }
	*/
/*	或者
    public function __construct()
    {
		parent::__construct();
        $this->dao = App::make(UserDao::class);
		
    }
*/	

    /**
     * 子类可根据需要覆盖 lifecycle
     */
    protected function initialize(): void
    {
		$this->dao = app(UserDao::class);
		// 注册租户ID获取回调
		/*
		BaseDao::initTenantCallback(function () {
			// 示例1：从请求头获取租户ID
			$request = app('request');
			return 2;
			return $request->headers->get('X-Tenant-Id', 0);

			// 示例2：从登录用户信息获取（需结合RBAC）
			// $user = app('auth')->user();
			// return $user->tenant_id ?? 0;
		});	
		*/
    }
	
	
	// Service 层示例
	public function getAllUsers()
	{
		// 忽略租户过滤，查询所有数据
		return $this->dao->ignoreTenant()->selectList([]);
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
		//dump($this->dao);
        return $this->dao->getListByTenantId($tenantId, $where);
    }
}